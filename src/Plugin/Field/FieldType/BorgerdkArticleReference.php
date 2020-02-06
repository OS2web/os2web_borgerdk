<?php

namespace Drupal\os2web_borgerdk\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\os2web_borgerdk\Entity\BorgerdkArticle;

/**
 * Provides a field type of baz.
 *
 * @FieldType(
 *   id = "os2web_borgerdk_article_reference",
 *   label = @Translation("OS2Web Borger.dk Article reference field"),
 *   module = "os2web_borgerdk",
 *   description = @Translation("Allows Borger.dk article to be attached via a field."),
 *   category = @Translation("Reference"),
 *   default_widget = "os2web_borgerdk_article_reference_widget",
 *   default_formatter = "os2web_borgerdk_article_reference_formatter",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class BorgerdkArticleReference extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => 'os2web_borgerdk_article',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the OS2Web Borger.dk entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'microarticle_ids' => [
          'description' => 'List of OS2Web Borger.dk selfservice entites IDs, saved as serialized value',
          'type' => 'text',
        ],
        'selfservice_ids' => [
          'description' => 'List of OS2Web Borger.dk selfservice entites IDs, saved as serialized value',
          'type' => 'text',
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'os2web_borgerdk_article',
          'columns' => ['target_id' => 'id'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['microarticle_ids'] = DataDefinition::create('string')
      ->setLabel(t('Microarticles'));

    $properties['selfservice_ids'] = DataDefinition::create('string')
      ->setLabel(t('Selfservices'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * Gets the Borger.dk article value saved in this field.
   *
   * @param bool $load
   *   If the article shall be loaded.
   *   TRUE by default.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   *   Borger.dk article or Borger.dk article ID.
   */
  public function getArticleValue($load = FALSE) {
    $value = $this->getValue();
    if (isset($value['target_id'])) {
      return ($load) ? BorgerdkArticle::load($value['target_id']) : $value['target_id'];
    }

    return NULL;
  }

  /**
   * Gets unserialized Borger.dk microarticle IDs value saved in this fields.
   *
   * @return array
   *   Array of Borger.dk microarticles.
   */
  public function getMicroarticleIdsValue() {
    $value = $this->getValue();
    if (isset($value['microarticle_ids'])) {
      return unserialize($value['microarticle_ids']);
    }
    return [];
  }

  /**
   * Gets unserialized Borger.dk selfservices IDs value saved in this fields.
   *
   * @return array
   *   Array of Borger.dk selfservices.
   */
  public function getSelfserviceIdsValue() {
    $value = $this->getValue();
    if (isset($value['selfservice_ids'])) {
      return unserialize($value['selfservice_ids']);
    }
    return [];
  }

}
