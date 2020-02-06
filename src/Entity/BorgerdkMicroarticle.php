<?php

namespace Drupal\os2web_borgerdk\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface;

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

    // Borger.dk - Microarticle Weight field.
    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The order of the microarticle, taken from Borger.dk'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    // Borger.dk - Microarticle Article reference field.
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
  public function getContent($strip_html = TRUE) {
    $content = $this->get('content')->value;
    return ($strip_html) ? strip_tags($content) : $content;
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

  /**
   * {@inheritdoc}
   */
  public function getSelfservices($load = TRUE) {
    $query = \Drupal::entityQuery('os2web_borgerdk_selfservice')
      ->condition('os2web_borgerdk_microarticle_id', $this->id());

    $ids = $query->execute();
    if (!empty($ids)) {
      return ($load) ? BorgerdkSelfservice::loadMultiple($ids) : $ids;
    }

    return [];
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
