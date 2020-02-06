<?php

namespace Drupal\os2web_borgerdk\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface;

/**
 * Defines the borger.dk selfservice entity class.
 *
 * @ContentEntityType(
 *   id = "os2web_borgerdk_selfservice",
 *   label = @Translation("Borger.dk selfservice"),
 *   label_collection = @Translation("Borger.dk selfservices"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\os2web_borgerdk\BorgerdkContentAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\os2web_borgerdk\Form\BorgerdkSelfserviceForm",
 *       "edit" = "Drupal\os2web_borgerdk\Form\BorgerdkSelfserviceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "os2web_borgerdk_selfservice",
 *   admin_permission = "administer os2web_borgerdk content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/os2web-borgerdk-selfservice/add",
 *     "canonical" = "/os2web_borgerdk_selfservice/{os2web_borgerdk_selfservice}",
 *     "edit-form" = "/admin/content/os2web-borgerdk-selfservice/{os2web_borgerdk_selfservice}/edit",
 *     "delete-form" = "/admin/content/os2web-borgerdk-selfservice/{os2web_borgerdk_selfservice}/delete",
 *     "collection" = "/admin/content/os2web-borgerdk-selfservice"
 *   },
 *   field_ui_base_route = "entity.os2web_borgerdk_selfservice.settings"
 * )
 */
class BorgerdkSelfservice extends BorgerdkContent implements BorgerdkSelfserviceInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    // Borger.dk - ID field.
    $fields['borgerdk_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Borger.dk ID'))
      ->setDescription(t('ID taken from Borger.dk.'))
      ->setSetting('max_length', 255);

    // Borger.dk - Selfservice label field.
    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The label the self-service, taken from Borger.dk.'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - Selfservice URL field.
    $fields['selfserviceUrl'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Selfservice URL'))
      ->setDescription(t('The URL of the self-service, taken from Borger.dk.'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Borger.dk - Selfservice Weight field.
    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The order of the selfservice, taken from Borger.dk'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    // Borger.dk - Selfservice Article reference field.
    $fields['os2web_borgerdk_article_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Article'))
      ->setDescription(t('Borger.dk parent article.'))
      ->setSetting('target_type', 'os2web_borgerdk_article')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    // Borger.dk - Selfservice Microarticle reference field.
    $fields['os2web_borgerdk_microarticle_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Microarticle'))
      ->setDescription(t('Borger.dk parent microarticle.'))
      ->setSetting('target_type', 'os2web_borgerdk_microarticle')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->get('selfserviceUrl')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getArticle($load = TRUE) {
    if ($fieldOs2webBorgerdkArticleId = $this->get('os2web_borgerdk_article_id')->first()) {
      if ($load) {
        return $fieldOs2webBorgerdkArticleId->get('entity')->getTarget()->getValue();
      }
      else {
        return $fieldOs2webBorgerdkArticleId->getValue()['target_id'];
      }
    }

    return NULL;
  }

}
