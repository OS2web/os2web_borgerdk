<?php

namespace Drupal\os2web_borgerdk\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\os2web_borgerdk\Entity\BorgerdkMicroarticle;
use Drupal\os2web_borgerdk\Entity\BorgerdkSelfservice;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "os2web_borgerdk_article_reference_formatter",
 *   module = "os2web_borgerdk",
 *   label = @Translation("Borger.dk article reference formatter"),
 *   field_types = {
 *     "os2web_borgerdk_article_reference"
 *   }
 * )
 */
class BorgerdkArticleReferenceFormatter extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'view_mode' => 'field_reference',
      'link' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $renderedArticles = parent::viewElements($items, $langcode);

    foreach ($items as $delta => $item) {
      /** @var \Drupal\os2web_borgerdk\Plugin\Field\FieldType\BorgerdkArticleReference $item */
      $elements[$delta]['article'] = $renderedArticles[$delta];

      $selectedMicroarticleIds = $item->getMicroarticleIdsValue();
      if (!empty($selectedMicroarticleIds)) {
        $elements[$delta]['microarticles'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => ['class' => 'os2web-borgerdk-microarticles'],
        ];

        $microarticle_view_builder = \Drupal::entityTypeManager()
          ->getViewBuilder('os2web_borgerdk_microarticle');
        $selectedMicroarticles = BorgerdkMicroarticle::loadMultiple($selectedMicroarticleIds);
        foreach ($selectedMicroarticles as $microarticle) {
          $elements[$delta]['microarticles'][$microarticle->id()] = $microarticle_view_builder->view($microarticle, 'field_reference');
        }
      }

      $selectedSelfserviceIds = $item->getSelfserviceIdsValue();
      if (!empty($selectedSelfserviceIds)) {
        $elements[$delta]['selfservices'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => ['class' => 'os2web-borgerdk-selfservices'],
        ];

        $selfservice_view_builder = \Drupal::entityTypeManager()
          ->getViewBuilder('os2web_borgerdk_selfservice');
        $selectedSelfservices = BorgerdkSelfservice::loadMultiple($selectedSelfserviceIds);
        foreach ($selectedSelfservices as $selfservice) {
          $elements[$delta]['selfservices'][$selfservice->id()] = $selfservice_view_builder->view($selfservice, 'field_reference');
        }
      }
    }

    return $elements;
  }

}
