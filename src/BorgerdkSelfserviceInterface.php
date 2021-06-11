<?php

namespace Drupal\os2web_borgerdk;

/**
 * Provides an interface defining a borger.dk selfservice entity type.
 */
interface BorgerdkSelfserviceInterface extends BorgerdkContentInterface {

  /**
   * Gets the borger.dk selfservice URL.
   *
   * @return string
   *   URL of the borger.dk selfservice.
   */
  public function getUrl();

  /**
   * Gets the borger.dk selfservice label.
   *
   * @return string
   *   Label of the borger.dk selfservice.
   */
  public function getLabel();

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
