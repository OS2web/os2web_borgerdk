<?php

namespace Drupal\os2web_borgerdk;

/**
 * Provides an interface defining a borger.dk selfservice entity type.
 */
interface BorgerdkSelfserviceInterface extends BorgerdkContentInterface {

  /**
   * Gets the borger.dk selfservice weight.
   *
   * @return int
   *   Weight of the borger.dk selfservice.
   */
  public function getWeight();

  /**
   * Sets the borger.dk selfservice weight.
   *
   * @param int $weight
   *   New selfservice weight.
   *
   * @return \Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface
   *   The called borger.dk selfservice.
   */
  public function setWeight($weight);

  /**
   * Gets the borger.dk selfservice URL.
   *
   * @return string
   *   URL of the borger.dk selfservice.
   */
  public function getUrl();

  /**
   * Gets the Borger.dk parent article.
   *
   * @param bool $load
   *   If article needs to be loaded.
   *
   * @return \Drupal\os2web_borgerdk\Entity\BorgerdkArticle|int
   *   Borger.dk article entity or article ID.
   */
  public function getArticle($load = TRUE);

}
