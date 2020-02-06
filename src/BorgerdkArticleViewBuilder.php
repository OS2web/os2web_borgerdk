<?php

namespace Drupal\os2web_borgerdk;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for feeds feed items.
 */
class BorgerdkArticleViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);

    if ($view_mode == 'full') {
      // Adding microarticles.
      $microarticles = $entity->getMicroarticles();
      if (!empty($microarticles)) {
        $build['microarticles'] = [
          '#theme' => 'item_list',
          '#title' => $this->t('Microarticles'),
          '#items' => [],
        ];

        foreach ($microarticles as $microarticle) {
          $build['microarticles']['#items'][$microarticle->id()] = [
            '#title' => $microarticle->label(),
            '#type' => 'link',
            '#url' => $microarticle->toUrl(),
          ];
        }
      }

      // Adding selfservices.
      $selfservices = $entity->getSelfservices();
      if (!empty($selfservices)) {
        $build['selfservices'] = [
          '#theme' => 'item_list',
          '#title' => $this->t('Selfservices'),
          '#items' => [],
        ];
        foreach ($selfservices as $selfservice) {
          $build['selfservices']['#items'][$selfservice->id()] = [
            '#title' => $selfservice->label(),
            '#type' => 'link',
            '#url' => $selfservice->toUrl(),
          ];
        }
      }
    }

    return $build;
  }

}
