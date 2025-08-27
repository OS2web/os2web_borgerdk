<?php

namespace Drupal\os2web_borgerdk\Plugin\migrate\source;

use BorgerDk\ArticleService\Client as ImportClient;
use BorgerDk\ArticleService\Resources\Endpoints\GetAllArticles;
use Drupal\os2web_borgerdk\BorgerDk\ArticleService\Resources\Endpoints\GetArticlesByIDs;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\os2web_borgerdk\BorgerdkArticleInterface;
use Drupal\os2web_borgerdk\Entity\BorgerdkArticle;
use Drupal\os2web_borgerdk\Entity\BorgerdkMicroarticle;
use Drupal\os2web_borgerdk\Entity\BorgerdkSelfservice;
use Drupal\os2web_borgerdk\Form\SettingsForm;

/**
 * Source plugin for OS2Web Borger.dk articles.
 *
 * @MigrateSource(
 *   id = "os2web_borgerdk_article"
 * )
 */
class BorgerdkArticleSource extends SourcePluginBase implements ConfigurableInterface {

  /**
   * Holds the obsolete articles Borger.dk ID.
   *
   * Obsolete means that an article was imported from the source in the past,
   * but it's is no longer present in the fresh import set.
   *
   * @var array
   */
  protected $borgerdkObsoleteArticleIds = [];

  /**
   * Holds the initiated import client, mapped by the client language.
   *
   * @var array
   */
  protected $importClients = [];

  /**
   * Holds Borger.dk articles source languages that are allowed to be imported.
   *
   * @var array
   */
  protected $importSources = [];

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\migrate\MigrateException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->setConfiguration($configuration);

    $settings = \Drupal::config(SettingsForm::$configName);

    // Import sources.
    foreach ($settings->get('import_sources') as $source => $sourceValue) {
      if ($sourceValue) {
        $this->importSources[] = $source;
      }
    }

    if (empty($this->importSources)) {
      throw new \InvalidArgumentException('You must have at least one language "da" or "en".');
    }

    // Initializing obsolete articles. At the beginning we treat all anonymously
    // authored articles as obsolete.
    // During intitializeIterator, the clean the list in a way that by the end
    // of the intitializeIterator  it consists solely of the actually obsolete
    // articles.
    $query = \Drupal::database()->select('os2web_borgerdk_article', 'ar')
      ->condition('ar.uid', 0)
      ->fields('ar', ['borgerdk_id', 'id']);
    $result = $query->execute();
    $this->borgerdkObsoleteArticleIds = $result->fetchAllKeyed();
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('Article ID'),
      'title' => $this->t('Article Title'),
      'url' => $this->t('Article URL'),
      'lastUpdated' => $this->t('Article Last Updated'),
      'publishDate' => $this->t('Article Publishing date'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
      ],
      'lang' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function __toString() {
    return $this->configuration['source'] . ', lang: ' . implode($this->configuration['lang'], ',');
  }

  /**
   * {@inheritDoc}
   */
  protected function initializeIterator() {
    $articles = [];

    foreach ($this->importSources as $lang) {
      $importClient = $this->getImportClient($lang);
      $articles_raw = new GetAllArticles($importClient);

      $articles_formatted = $articles_raw->getResultFormatted();

      // Converting each article to array.
      foreach ($articles_formatted as $articleId => $article) {
        // Saving article lang for future references.
        $article->lang = $lang;

        // Unsetting this article from a list of obsolete Borger.dk articles, as
        // it's still present in the source.
        unset($this->borgerdkObsoleteArticleIds[$article->id]);

        $articles[] = (array) $article;
      }
    }

    // Process obsolete articles.
    $this->processObsoleteArticles();

    // Creating array object in order to return iterator.
    $articleObj = new \ArrayObject($articles);

    return $articleObj->getIterator();
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    $result = parent::prepareRow($row);

    // If this row is to be skipped, return the result right away.
    if (!$result) {
      return $result;
    }

    // Check if the current article needs creating updating.
    if (!$row->getIdMap() || $row->needsUpdate() || $this->aboveHighwater($row) || $this->rowChanged($row)) {
      $articleId = $row->getSourceProperty('id');
      $articleLang = $row->getSourceProperty('lang');

      // Getting Existing Borger.dk article, if present.
      /** @var \Drupal\os2web_borgerdk\BorgerdkArticleInterface $borgerdkArticle */
      $borgerdkArticle = BorgerdkArticle::loadByBorgerdkId($articleId, $articleLang);

      // Getting full loaded article.
      $importClient = $this->getImportClient($articleLang);
      $params = ['articleIDs' => [$articleId]];
      if ($municipalityCode = $row->getSourceProperty('municipalityCode')) {
        $params['municipalityCode'] = $municipalityCode;
      }

      $articles_resource = new GetArticlesByIDs($importClient, $params);
      $articles_formatted = $articles_resource->getResultFormatted();
      $articleFormatted = reset($articles_formatted);

      // Filling row simple fields.
      $row->setSourceProperty('header', $articleFormatted->header);
      $row->setSourceProperty('legislation', $articleFormatted->legislation->content);
      $row->setSourceProperty('recommendation', $articleFormatted->recommendation->content);
      $row->setSourceProperty('byline', $articleFormatted->byline);

      // Getting previously imported microarticles and selfservices.
      $prevArticleMicroarticlesToDelete = $prevArticleSelfservicesToDelete = [];
      if ($borgerdkArticle) {
        $prevArticleMicroarticlesToDelete = $borgerdkArticle->getMicroarticles(TRUE, ['uid' => 0]);
        $prevArticleSelfservicesToDelete = $borgerdkArticle->getSelfservices(TRUE, ['uid' => 0]);
      }

      // Creating/updating microarticles.
      $articleMicroarticles = [];
      $microarticleTargets = [];
      if ($microarticles = $articleFormatted->microArticles) {
        foreach ($microarticles as $microarticle) {
          $entity = BorgerdkMicroarticle::loadByBorgerdkId($microarticle->id);
          if (!$entity) {
            $entity = BorgerdkMicroarticle::create([
              'borgerdk_id' => $microarticle->id,
              'title' => $microarticle->headline,
              'uid' => ['target_id' => 0],
              'lang' => $articleLang,
            ]);
          }

          $entity->setTitle($microarticle->headline);
          $entity->set('content', [
            'value' => $microarticle->content,
            'format' => 'wysiwyg_tekst',
          ]);
          $entity->set('source', 'Borger.dk');

          $entity->save();
          // Unsetting this microarticle form the list of previously imported
          // microarticles.
          unset($prevArticleMicroarticlesToDelete[$entity->id()]);

          // Saving microarticles to quickly find if any selfservice is
          // related with it.
          $articleMicroarticles[$microarticle->headline] = $entity;

          $microarticleTargets[] = ['target_id' => $entity->id()];
        }
      }
      // Putting custom MM to the end.
      if ($borgerdkArticle) {
        $customMaIds = $borgerdkArticle->getMicroarticles(FALSE, ['uid' => [0, '<>']]);
        foreach ($customMaIds as $id) {
          $microarticleTargets[] = ['target_id' => $id];
        }
      }

      $row->setSourceProperty('article_microarticle_targets', $microarticleTargets);

      // Creating/updating selfservices.
      $selfserviceTargets = [];
      if ($selfservices = $articleFormatted->selfServiceLinks) {
        foreach ($selfservices as $selfservice) {
          $entity = BorgerdkSelfservice::loadByBorgerdkId($selfservice->id);

          if (!$entity) {
            $entity = BorgerdkSelfservice::create([
              'borgerdk_id' => $selfservice->id,
              'title' => $selfservice->title,
              'label' => $selfservice->label,
              'selfserviceUrl' => $selfservice->url,
              'uid' => ['target_id' => 0],
              'lang' => $articleLang,
            ]);
          }

          $entity->setTitle($selfservice->title);
          $entity->set('label', $selfservice->label);
          $entity->set('selfserviceUrl', $selfservice->url);
          $entity->set('source', 'Borger.dk');

          $entity->save();

          // Unsetting this selfservice form the list of previously imported
          // selfservices.
          unset($prevArticleSelfservicesToDelete[$entity->id()]);

          // Check if we have a microarticle with the similar title.
          $relatedMaName = preg_replace('/(Start\s)/i', '', $selfservice->title);
          if (array_key_exists($relatedMaName, $articleMicroarticles)) {
            /** @var \Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface $relatedMicroarticle */
            $relatedMicroarticle = $articleMicroarticles[$relatedMaName];
            $relatedMicroarticle->addSelfservice($entity);
          }

          $selfserviceTargets[] = ['target_id' => $entity->id()];
        }
      }
      // Putting custom SS to the end.
      if ($borgerdkArticle) {
        $customSsIds = $borgerdkArticle->getSelfservices(FALSE, ['uid' => [0, '<>']]);
        foreach ($customSsIds as $id) {
          $selfserviceTargets[] = ['target_id' => $id];
        }
      }

      $row->setSourceProperty('article_selfservice_targets', $selfserviceTargets);

      // Deleting microarticles that are still in the list - the are no longer
      // present in article.
      foreach ($prevArticleMicroarticlesToDelete as $maToDelete) {
        $maToDelete->delete();
      }

      // Deleting selfservice that are still in the list - the are no longer
      // present in article.
      foreach ($prevArticleSelfservicesToDelete as $ssToDelete) {
        $ssToDelete->delete();
      }
    }

    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfiguration(array $configuration) {
    // We must preserve integer keys for column_name mapping.
    $this->configuration = NestedArray::mergeDeepArray([$this->defaultConfiguration(), $configuration], TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    $municipalityCode = \Drupal::config(SettingsForm::$configName)->get('selected_municipality');
    if (!isset($municipalityCode)) {
      $municipalityCode = 0;
    }
    return [
      'municipalityCode' => $municipalityCode,
    ];
  }

  /**
   * Gets an existing Borger.dk import client.
   *
   * If client is not initialized it, it will be initialized and added to a list
   * for future references.
   *
   * @param string $lang
   *   Language of the client, either 'da' or 'en'.
   *
   * @return \BorgerDk\ArticleService\Client
   *   Fully initialized client.
   *
   * @throws \BorgerDk\ArticleService\Exceptions\SoapException
   */
  protected function getImportClient($lang = 'da') {
    if (empty($this->importClients) || !array_key_exists($lang, $this->importClients)) {
      $importClient = new ImportClient($lang);
      $this->importClients[$lang] = $importClient;
    }

    return $this->importClients[$lang];
  }

  /**
   * Processes the obsolete articles.
   *
   * When article is deleted from a source but still present in the local
   * version, it makes it obsolete.
   * Obsolete articles need to notify the related nodes' authors (if the
   * setting is on).
   */
  protected function processObsoleteArticles() {
    if (!empty($this->borgerdkObsoleteArticleIds)) {
      foreach ($this->borgerdkObsoleteArticleIds as $article_borgerdk_id => $article_id) {
        // Ignore the article if we don't have Borger.dk ID.
        if (!$article_borgerdk_id) {
          continue;
        }

        /** @var \Drupal\os2web_borgerdk\BorgerdkArticleInterface $article */
        $article = BorgerdkArticle::load($article_id);

        // Check if we want to send notifications.
        if (\Drupal::config(SettingsForm::$configName)->get('obsolete_notification_enabled')) {
          $affectedEntities = $this->getAffectedEntities($article);
          // If there are affected entities, notify about them.
          if (!empty($affectedEntities)) {
            $this->sendObsoleteEntitiesEmail($article, $affectedEntities);
          }
        }
      }
    }
  }

  /**
   * Gets the list of entities that are referencing this Borger.dk article.
   *
   * Reference is checked via "os2web_borgerdk_article_reference" field.
   *
   * @param \Drupal\os2web_borgerdk\BorgerdkArticleInterface $article
   *   Borger.dk article to find references of.
   *
   * @return array
   *   Array of loaded entities, that are referencing this Borger.dk article.
   *   Formatted like this:
   *   [
   *     [node] => [
   *       [nid_1] => fully loaded node 1,
   *       [nid_2] => fully loaded node 2,
   *     ],
   *     [taxonomy_term] => [
   *       [tid_4] => fully loaded taxonomy term,
   *     ],
   *     [custom_entity_type] => [
   *       [id] => fully loaded entity,
   *     ],
   *     ...
   *   ]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getAffectedEntities(BorgerdkArticleInterface $article) {
    $affectedEntities = [];

    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fieldMap = $entityFieldManager->getFieldMapByFieldType('os2web_borgerdk_article_reference');

    foreach ($fieldMap as $entityType => $fieldUsages) {
      foreach ($fieldUsages as $fieldName => $fieldUsage) {
        $ids = \Drupal::entityQuery($entityType)
          ->accessCheck(false)
          ->condition($fieldName, $article->id())->execute();

        if (!empty($ids)) {
          $affectedEntities[$entityType] =
            \Drupal::entityTypeManager()
              ->getStorage($entityType)
              ->loadMultiple($ids);
        }
      }
    }

    return $affectedEntities;
  }

  /**
   * Makes the actual sending of the email.
   *
   * Fetches the templates, fills in the replacements tokens and handles the
   * email sending.
   *
   * @param \Drupal\os2web_borgerdk\BorgerdkArticleInterface $article
   *   Borger.dk article the we are notifying about.
   * @param array $affectedEntities
   *   List of the affected entities by this Borger.dk article.
   *   Formatted like this:
   *   [
   *     [node] => [
   *       [nid_1] => fully loaded node 1,
   *       [nid_2] => fully loaded node 2,
   *     ],
   *     [taxonomy_term] => [
   *       [tid_4] => fully loaded taxonomy term,
   *     ],
   *     [custom_entity_type] => [
   *       [id] => fully loaded entity,
   *     ],
   *     ...
   *   ].
   */
  protected function sendObsoleteEntitiesEmail(BorgerdkArticleInterface $article, array $affectedEntities) {
    // Getting settings params.
    $recipients = \Drupal::config(SettingsForm::$configName)->get('obsolete_notification_recipients');
    $subject_template = \Drupal::config(SettingsForm::$configName)->get('obsolete_notification_email_subject');
    $body_template = \Drupal::config(SettingsForm::$configName)->get('obsolete_notification_email_body');

    // Generating list of entities.
    $affectedEntitiesHtml = NULL;

    foreach ($affectedEntities as $entityType => $entities) {
      foreach ($entities as $entity) {
        $affectedEntitiesHtml .= '- ' . $entity->label() . ': ' . $entity->toUrl('canonical', ['absolute' => TRUE])->toString() . PHP_EOL;
      }
    }

    // Composing search/replace.
    $search = ['!article_title', '!entities'];
    $replace = [$article->label(), $affectedEntitiesHtml];

    // Making replacements.
    $subject = str_replace($search, $replace, $subject_template);
    $subject = ucfirst($subject);
    $body = str_replace($search, $replace, $body_template);
    $body = ucfirst($body);

    $siteName = \Drupal::config('system.site')->get('name');
    $siteMail = \Drupal::config('system.site')->get('mail');
    $messageVariables = [
      'to' => $recipients,
      'from' => "$siteName <$siteMail>",
      'subject' => $subject,
      'body' => $body,
    ];

    /** @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

    if (!$mailManager->mail('os2web_borgerdk', 'os2web_borgerdk_mail', $recipients, $langcode, $messageVariables)) {
      \Drupal::logger('os2web_borgerdk')->warning(t('There was a problem sending email to %email', ['%email' => $recipients]));
    }
  }

}
