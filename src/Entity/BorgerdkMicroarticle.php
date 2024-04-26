<?php

namespace Drupal\os2web_borgerdk\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface;
use Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface;

/**
 * Defines the borger.dk microarticle entity class.
 *
 * @ContentEntityType(
 *   id = "os2web_borgerdk_microarticle",
 *   label = @Translation("Borger.dk microarticle"),
 *   label_collection = @Translation("Borger.dk microarticles"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\os2web_borgerdk\BorgerdkContentAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\os2web_borgerdk\Form\BorgerdkMicroarticleForm",
 *       "edit" = "Drupal\os2web_borgerdk\Form\BorgerdkMicroarticleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "os2web_borgerdk_microarticle",
 *   admin_permission = "administer os2web_borgerdk content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/os2web-borgerdk-microarticle/add",
 *     "canonical" = "/os2web_borgerdk_microarticle/{os2web_borgerdk_microarticle}",
 *     "edit-form" = "/admin/content/os2web-borgerdk-microarticle/{os2web_borgerdk_microarticle}/edit",
 *     "delete-form" = "/admin/content/os2web-borgerdk-microarticle/{os2web_borgerdk_microarticle}/delete",
 *     "collection" = "/admin/content/os2web-borgerdk-microarticle"
 *   },
 *   field_ui_base_route = "entity.os2web_borgerdk_microarticle.settings"
 * )
 */
class BorgerdkMicroarticle extends BorgerdkContent implements BorgerdkMicroarticleInterface {

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

    // Borger.dk - Microarticle Content field.
    $fields['content'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Content'))
      ->setDescription(t('Content of the microarticle, taken from Borger.dk.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

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

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent($strip_html = TRUE) {
    $content = $this->get('content')->value;
    return ($strip_html) ? strip_tags($content) : $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getArticle($load = TRUE) {
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'os2web_borgerdk_article')
      ->condition('os2web_borgerdk_microarticles', $this->id());

    $ids = $query->execute();
    if (!empty($nids)) {
      $id = reset($ids);
      return ($load) ? BorgerdkArticle::load($id) : $id;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelfservices($load = TRUE) {
    if ($fieldSS = $this->get('os2web_borgerdk_selfservices')) {
      if ($load) {
        return $fieldSS->referencedEntities();
      }
      else {
        return array_column($fieldSS->getValue(), 'target_id');
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function addSelfservice(BorgerdkSelfserviceInterface $selfservice, $save = TRUE) {
    $selfservices = $this->get('os2web_borgerdk_selfservices')->getValue();
    $key = array_search($selfservice->id(), array_column($selfservices, 'target_id'));
    if ($key === FALSE) {
      $this->get('os2web_borgerdk_selfservices')->appendItem($selfservice->id());
      if ($save) {
        $this->save();
      }
    }
  }

  /**
   * Also deletes the related selfservices.
   *
   * @see \Drupal\Core\Entity:delete()
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function delete() {
    $selfServices = $this->getSelfservices();
    if (!empty($selfServices)) {
      foreach ($selfServices as $selfService) {
        $selfService->delete();
      }
    }

    parent::delete();
  }

}
