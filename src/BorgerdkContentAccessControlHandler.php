<?php

namespace Drupal\os2web_borgerdk;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the os2web_borgerdk content.
 */
class BorgerdkContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view os2web_borgerdk content');

      case 'update':
        if ($account->hasPermission('administer os2web_borgerdk content')) {
          return AccessResult::allowed();
        }

        if ($account->hasPermission('edit own os2web_borgerdk content')) {
          if ($account->id() === $entity->getOwnerId()) {
            return AccessResult::allowed();
          }
        }

      case 'delete':
        if ($account->hasPermission('administer os2web_borgerdk content')) {
          return AccessResult::allowed();
        }

        if ($account->hasPermission('delete own os2web_borgerdk content')) {
          if ($account->id() === $entity->getOwnerId()) {
            return AccessResult::allowed();
          }
        }

      default:
        // No opinion.
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create os2web_borgerdk content', 'administer os2web_borgerdk content'], 'OR');
  }

}
