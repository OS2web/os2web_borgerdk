<?php

namespace Drupal\os2web_borgerdk;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines interface to be implements by Borger.dk content.
 */
interface BorgerdkContentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the borger.dk content title.
   *
   * @return string
   *   Title of the borger.dk content.
   */
  public function getTitle();

  /**
   * Sets the borger.dk content title.
   *
   * @param string $title
   *   The borger.dk content title.
   *
   * @return \Drupal\os2web_borgerdk\BorgerdkContentInterface
   *   The called borger.dk content entity.
   */
  public function setTitle($title);

  /**
   * Gets the borger.dk content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the borger.dk content.
   */
  public function getCreatedTime();

  /**
   * Sets the borger.dk content creation timestamp.
   *
   * @param int $timestamp
   *   The borger.dk content creation timestamp.
   *
   * @return \Drupal\os2web_borgerdk\BorgerdkContentInterface
   *   The called borger.dk content entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the borger.dk content status.
   *
   * @return bool
   *   TRUE if the borger.dk content is published, FALSE otherwise.
   */
  public function isPublished();

  /**
   * Sets the borger.dk content status.
   *
   * @param bool $status
   *   TRUE to enable this borger.dk content, FALSE to disable.
   *
   * @return \Drupal\os2web_borgerdk\BorgerdkContentInterface
   *   The called borger.dk content entity.
   */
  public function setStatus($status);

  /**
   * Loads the entity by it's Borger.dk ID.
   *
   * @param string $borgerdkID
   *   Borger.dk ID.
   * @param string $lang
   *   Language of the Borger.dk entity source.
   *
   * @return \Drupal\os2web_borgerdk\BorgerdkContentInterface|null
   *   The found borger.dk content entity or NULL.
   */
  public static function loadByBorgerdkId($borgerdkID, $lang);

  /**
   * Adds conditions to the Query.
   *
   * If list of condition parameters is empty, nothing is done,
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   Query to which to add the conditional params.
   * @param array $conditionParams
   *   Array of conditions to be added to the query.
   *   Accepts two way of formats, which can also be mixed:
   *   [
   *     'field_name1' => 'value1',
   *     ...
   *   ]
   *   or
   *   [
   *     'field_name1' => [
   *       0 => 'value1',
   *       1 => 'operator1'
   *     ],
   *     ...
   *   ].
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   Returns the query with the added conditional parameters.
   */
  public function addQueryConditions(QueryInterface $query, array $conditionParams);

}
