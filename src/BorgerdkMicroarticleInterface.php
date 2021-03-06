<?php

namespace Drupal\os2web_borgerdk;

/**
 * Provides an interface defining a borger.dk microarticle entity type.
 */
interface BorgerdkMicroarticleInterface extends BorgerdkContentInterface {

  /**
   * Gets the borger.dk microarticle content.
   *
   * @return string
   *   Content of the borger.dk microarticle.
   */

  /**
   * Gets the borger.dk microarticle content.
   *
   * @param bool $strip_html
   *   If the HTML tags should be stripped from the returned content.
   *   TRUE by default.
   *
   * @return string
   *   Content of the borger.dk microarticle.
   */
  public function getContent($strip_html = TRUE);

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

  /**
   * Gets the Borger.dk selfservices attached to this article.
   *
   * @param bool $load
   *   If the returned entities shall be load. If FALSE, array of ids are
   *   returned.
   *
   * @return array
   *   If load is TRUE array of BorgerdkSelfsevice is returned,
   *   If load is FALSE array of ids is returned,
   *   If field is empty, empty array is returned.
   */
  public function getSelfservices($load = TRUE);

  /**
   * Adds the selfservice to this microarticle.
   *
   * Only does so if the selfservice is not already added.
   * Saves the microarticle as well.
   *
   * @param \Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface $selfservice
   *   Selfservice to add.
   * @param bool $save
   *   If bullet point needs to be saved right away.
   */
  public function addSelfservice(BorgerdkSelfserviceInterface $selfservice, $save = TRUE);

}
