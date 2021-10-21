<?php

/**
 * Overridde class for implementation of GetArticlesByIDs method
 */

namespace Drupal\os2web_borgerdk\BorgerDk\ArticleService\Resources\Endpoints;

use BorgerDk\ArticleService\Resources\Endpoints\GetArticlesByIDs as BaseGetArticlesByIDs;
use Drupal\os2web_borgerdk\Form\SettingsForm;

/**
 * Class BorgerdkGetArticlesByIDs
 */
class GetArticlesByIDs extends BaseGetArticlesByIDs {

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function formatSingleArticle($article) {
    if (!empty($this->getReplacements())) {
      foreach ($this->getReplacements() as $replace_from => $replace_to) {
        $article->Content = str_replace($replace_from, $replace_to, $article->Content);
      }
    }
    return parent::formatSingleArticle($article);
  }

  /**
   * Retrieves a configuration object.
   */
  protected function config() {
    if (!$this->config) {
      $this->config = \Drupal::service('config.factory')->get(SettingsForm::$configName);
    }

    return $this->config;
  }

  /**
   * Gets replacements from configuration.
   *
   * @return array
   *   The array of replacements key/value pairs.
   */
  protected function getReplacements() {
    $values = [];
    $string = $this->config()->get('replacements');

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) {
      $matches = [];
      if (!preg_match('/(.*)\|(.*)/', $text, $matches)) {
        continue;
      }

      if (count($matches) < 2) {
        continue;
      }

      // Trim key and value to avoid unwanted spaces issues.
      $key = trim($matches[1]);
      $value = trim($matches[2]);
      $values[$key] = $value;
    }

    return $values;
  }

}
