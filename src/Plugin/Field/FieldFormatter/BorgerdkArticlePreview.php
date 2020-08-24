<?php

namespace Drupal\os2web_borgerdk\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "os2web_borgerdk_article_preview",
 *   module = "os2web_borgerdk",
 *   label = @Translation("Borger.dk article preview"),
 *   field_types = {
 *     "os2web_borgerdk_article_reference"
 *   }
 * )
 */
class BorgerdkArticlePreview extends EntityReferenceEntityFormatter {

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

    foreach ($items as $delta => $item) {
      /** @var \Drupal\os2web_borgerdk\Plugin\Field\FieldType\BorgerdkArticleReference $item */
      $article = $item->getArticleValue(TRUE);
      $elements[$delta]['article'] = [
        '#markup' => $article->label(),
      ];
    }

    return $elements;
  }

}
