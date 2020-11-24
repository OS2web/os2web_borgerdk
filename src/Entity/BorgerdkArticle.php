<?php

namespace Drupal\os2web_borgerdk\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
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

    // PreText
    $fields['pre_text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Tekst i toppen'))
      ->setDescription(t('Tekst i toppen af Borger.dk artikkel'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('view', TRUE);

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

    // Borger.dk - Microarticles reference field.
    $fields['os2web_borgerdk_microarticles'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Microarticles'))
      ->setDescription(t('Borger.dk microarticles'))
      ->setSetting('target_type', 'os2web_borgerdk_microarticle')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    // Borger.dk - Selfservice reference field.
    $fields['os2web_borgerdk_selfservices'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Selfservices'))
      ->setDescription(t('Borger.dk selfservices'))
      ->setSetting('target_type', 'os2web_borgerdk_selfservice')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

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

    // PostText field.
    $fields['post_text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Tekst i bunden'))
      ->setDescription(t('Tekst i bunden af Borger.dk artikkel'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getMicroarticles($load = TRUE, array $conditionParams = []) {
    if ($fieldMas = $this->get('os2web_borgerdk_microarticles')) {
      $microarticleIds = array_column($fieldMas->getValue(), 'target_id');

      if (!empty($microarticleIds)) {
        $query = \Drupal::entityQuery('os2web_borgerdk_microarticle')
          ->condition('id', $microarticleIds, 'IN');

        $query = $this->addQueryConditions($query, $conditionParams);
        $ids = $query->execute();

        if (!empty($ids)) {
          // Ordering IDs so that the are sorted according to MA delta in
          // Article.
          $orderedIds = [];
          foreach ($microarticleIds as $microarticleId) {
            if (in_array($microarticleId, $ids)) {
              $orderedIds[] = $microarticleId;
            }
          }

          return ($load) ? BorgerdkMicroarticle::loadMultiple($orderedIds) : $orderedIds;
        }
      }
    }

    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getSelfservices($load = TRUE, array $conditionParams = []) {
    if ($fieldSS = $this->get('os2web_borgerdk_selfservices')) {
      $selfserviceIds = array_column($fieldSS->getValue(), 'target_id');

      if (!empty($selfserviceIds)) {
        $query = \Drupal::entityQuery('os2web_borgerdk_selfservice')
          ->condition('id', $selfserviceIds, 'IN');

        $query = $this->addQueryConditions($query, $conditionParams);

        $ids = $query->execute();
        if (!empty($ids)) {
          // Ordering IDs so that the are sorted according to SS delta in
          // Article.
          $orderedIds = [];
          foreach ($selfserviceIds as $selfserviceId) {
            if (in_array($selfserviceId, $ids)) {
              $orderedIds[] = $selfserviceId;
            }
          }

          return ($load) ? BorgerdkSelfservice::loadMultiple($orderedIds) : $orderedIds;
        }
      }
    }

    return [];
  }

  /**
   * Updates custom microarticles and selfservices delta.
   *
   * @see \Drupal\Core\Entity:save()
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save() {
    if (!$this->isNew()) {
      $this->updateCustomMicroarticlesDelta();
      $this->updateCustomSelfservicesDelta();
    }

    return parent::save();
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
   * Updates the delta of custom microarticles attached to this article.
   *
   * Orders the attached microarticles, so that all custom MA are put to the
   * end.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateCustomMicroarticlesDelta() {
    $importedMaIds = $this->getMicroarticles(FALSE, ['uid' => 0]);

    $orderedMaTargets = [];
    if (!empty($importedMaIds)) {
      foreach ($importedMaIds as $id) {
        $orderedMaTargets[] = ['target_id' => $id];
      }

      $customMaIds = $this->getMicroarticles(FALSE, ['uid' => [0, '<>']]);
      foreach ($customMaIds as $id) {
        $orderedMaTargets[] = ['target_id' => $id];
      }

      $this->set('os2web_borgerdk_microarticles', $orderedMaTargets);
    }
  }

  /**
   * Updates the delta of custom selfservices attached to this article.
   *
   * Orders the attached selfservices, so that all custom SS are put to the
   * end.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateCustomSelfservicesDelta() {
    $importedSsIds = $this->getSelfservices(FALSE, ['uid' => 0]);

    $orderedSsTargets = [];
    if (!empty($importedSsIds)) {
      foreach ($importedSsIds as $id) {
        $orderedSsTargets[] = ['target_id' => $id];
      }

      $customSsIds = $this->getSelfservices(FALSE, ['uid' => [0, '<>']]);
      foreach ($customSsIds as $id) {
        $orderedSsTargets[] = ['target_id' => $id];
      }

      $this->set('os2web_borgerdk_selfservices', $orderedSsTargets);
    }
  }

}
