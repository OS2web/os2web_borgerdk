<?php

namespace Drupal\os2web_borgerdk\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\os2web_borgerdk\BorgerdkArticleInterface;

/**
 * Defines the os2web_borgerdk_article entity class.
 *
 * @ContentEntityType(
 *   id = "os2web_borgerdk_article",
 *   label = @Translation("Borger.dk Article"),
 *   label_collection = @Translation("Borger.dk Articles"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "view_builder" = "Drupal\os2web_borgerdk\BorgerdkArticleViewBuilder",
 *     "access" = "Drupal\os2web_borgerdk\BorgerdkContentAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\os2web_borgerdk\Form\BorgerdkArticleForm",
 *       "edit" = "Drupal\os2web_borgerdk\Form\BorgerdkArticleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "os2web_borgerdk_article",
 *   admin_permission = "administer os2web_borgerdk content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/os2web-borgerdk-article/add",
 *     "canonical" = "/os2web_borgerdk_article/{os2web_borgerdk_article}",
 *     "edit-form" = "/admin/content/os2web-borgerdk-article/{os2web_borgerdk_article}/edit",
 *     "delete-form" = "/admin/content/os2web-borgerdk-article/{os2web_borgerdk_article}/delete",
 *     "collection" = "/admin/content/os2web-borgerdk-article"
 *   },
 *   field_ui_base_route = "entity.os2web_borgerdk_article.settings"
 * )
 */
class BorgerdkArticle extends BorgerdkContent implements BorgerdkArticleInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    // Borger.dk - ID field.
    $fields['borgerdk_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Borger.dk ID'))
      ->setDescription(t('ID taken from Borger.dk.'));

    // Borger.dk - ArticleHeader field.
    $fields['header'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Header'))
      ->setDescription(t('A header for the item, taken from Borger.dk.'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - ArticleUrl field.
    $fields['articleUrl'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Article URL'))
      ->setDescription(t('The URL of the article, taken from Borger.dk.'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - Byline field.
    $fields['byline'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Byline'))
      ->setDescription(t('The byline of the article, taken from Borger.dk.'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - Lovgining field.
    $fields['legislation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Legislation'))
      ->setDescription(t('Legislation section of article, taken from Borger.dk.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - Anbefaler field.
    $fields['recommendation'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Recommendation'))
      ->setDescription(t('Recommendation section of article, taken from Borger.dk.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - LastUpdated field.
    $fields['lastUpdated'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Last updated'))
      ->setDescription(t('The Unix timestamp of the entity last updated date, taken from Borger.dk'))
      ->setDefaultValue(0);

    // Borger.dk - PublishingDate field.
    $fields['publishingDate'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Publishing date'))
      ->setDescription(t('The Unix timestamp of the entity publishing date, taken from Borger.dk'))
      ->setDefaultValue(0);

    // Borger.dk - LastUpdated field.
    $fields['municipality_code'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Municipality code'))
      ->setDescription(t('The code of the municipality which this Borger.dk article was imported from.'))
      ->setDefaultValue(0);

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getMicroarticles($load = TRUE, array $conditionParams = []) {
    $query = \Drupal::entityQuery('os2web_borgerdk_microarticle')
      ->condition('os2web_borgerdk_article_id', $this->id())
      ->sort('weight');

    $query = $this->addQueryConditions($query, $conditionParams);

    $ids = $query->execute();
    if (!empty($ids)) {
      return ($load) ? BorgerdkMicroarticle::loadMultiple($ids) : $ids;
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getSelfservices($load = TRUE, array $conditionParams = []) {
    $query = \Drupal::entityQuery('os2web_borgerdk_selfservice')
      ->condition('os2web_borgerdk_article_id', $this->id())
      ->sort('weight');

    $query = $this->addQueryConditions($query, $conditionParams);

    $ids = $query->execute();
    if (!empty($ids)) {
      return ($load) ? BorgerdkSelfservice::loadMultiple($ids) : $ids;
    }

    return [];
  }

  /**
   * Updates custom microarticles and selfservices weight.
   *
   * @see \Drupal\Core\Entity:save()
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save() {
    if (!$this->isNew()) {
      $this->updateCustomMicroarticlesWeight();
      $this->updateCustomSelfservicesWeight();
    }

    return parent::save();
  }

  /**
   * Custom postSave handler.
   *
   * Updates the references in connected microarticles and selfservices if they
   * were created the article itself.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param bool $update
   *   TRUE if the entity has been updated, or FALSE if it has been inserted.
   *
   * @see \Drupal\os2web_borgerdk\Plugin\migrate\source\BorgerdkArticle::prepareRow()
   * @see \Drupal\Core\Entity\ContentEntityBase::postSave()
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    if (!$update) {
      // Updating microarticles.
      if ($migrate_microarticles = $this->migrate_article_microarticles) {
        foreach ($migrate_microarticles as $migrate_microarticle) {
          $migrate_microarticle->set('os2web_borgerdk_article_id', $this->id());
          $migrate_microarticle->save();
        }
        unset($this->migrate_article_microarticles);
      }

      // Updating selfservices.
      if ($migrate_selfservices = $this->migrate_article_selfservices) {
        foreach ($migrate_selfservices as $migrate_selfservice) {
          $migrate_selfservice->set('os2web_borgerdk_article_id', $this->id());
          $migrate_selfservice->save();
        }
        unset($this->migrate_article_selfservices);
      }
    }

    parent::postSave($storage, $update);
  }

  /**
   * Also deletes the related microarticles and selfservices.
   *
   * @see \Drupal\Core\Entity:delete()
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function delete() {
    // Deleting microarticles.
    $microarticles = $this->getMicroarticles();
    if (!empty($microarticles)) {
      foreach ($microarticles as $microarticle) {
        $microarticle->delete();
      }
    }

    // Deleting selfservices.
    $selfServices = $this->getSelfservices();
    if (!empty($selfServices)) {
      foreach ($selfServices as $selfService) {
        $selfService->delete();
      }
    }

    parent::delete();
  }

  /**
   * Updates the weight of custom microarticles attached to this article.
   *
   * Does this by retrieving the weight of the last imported microarticle (also
   * the highest weight), then looping though each custom microarticle and
   * setting its weight as 1 higher than the previous microarticle weight.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateCustomMicroarticlesWeight() {
    // Get last imported microarticle weight.
    $lastWeight = 0;
    $importedMaIds = $this->getMicroarticles(FALSE, ['uid' => 0]);

    if (!empty($importedMaIds)) {
      $highestWeightMaId = end($importedMaIds);
      /** @var \Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface $lastMa */
      $lastMa = BorgerdkMicroarticle::load($highestWeightMaId);

      // Getting last microarticle weight.
      $lastWeight = $lastMa->getWeight();
      $lastWeight++;
    }

    // Updating custom microarticles.
    $customMicroarticles = $this->getMicroarticles(TRUE, ['uid' => [0, '<>']]);
    foreach ($customMicroarticles as $customMa) {
      $customMa->setWeight($lastWeight);
      $customMa->save();
      $lastWeight++;
    }
  }

  /**
   * Updates the weight of custom selfservices attached to this article.
   *
   * Does this by retrieving the weight of the last imported selfservice (also
   * the highest weight), then looping though each custom selfservice and
   * setting its weight as 1 higher than the previous selfservice weight.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateCustomSelfservicesWeight() {
    // Get last imported selfservice weight.
    $lastWeight = 0;
    $importedSsIds = $this->getSelfservices(FALSE, ['uid' => 0]);

    if (!empty($importedSsIds)) {
      $heighestWeightSsId = end($importedSsIds);
      /** @var \Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface $lastSs */
      $lastSs = BorgerdkSelfservice::load($heighestWeightSsId);

      // Getting last selfservice weight.
      $lastWeight = $lastSs->getWeight();
      $lastWeight++;
    }

    // Updating custom selfservices.
    $customSelfservices = $this->getSelfservices(TRUE, ['uid' => [0, '<>']]);
    foreach ($customSelfservices as $customSs) {
      $customSs->setWeight($lastWeight);
      $customSs->save();
      $lastWeight++;
    }
  }

}
