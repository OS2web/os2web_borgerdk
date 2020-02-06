<?php

namespace Drupal\os2web_borgerdk;

/**
 * Provides an interface defining a os2web_borgerdk_article entity type.
 */
interface BorgerdkArticleInterface extends BorgerdkContentInterface {

  /**
   * Gets the Borger.dk microarticles attached to article ordered by weight.
   *
   * @param bool $load
   *   If the returned entities shall be load. If FALSE, array of ids are
   *   returned.
   * @param array $conditionParams
   *   Array of the additional conditions that are passed to the entityQuery.
   *
   * @return array
   *   If load is TRUE array of BorgerdkMicroarticle is returned,
   *   If load is FALSE array of ids is returned,
   *   If field is empty, empty array is returned.
   */
  public function getMicroarticles($load = TRUE, array $conditionParams = []);

  /**
   * Gets the Borger.dk selfservices attached to article ordered by weight.
   *
   * @param bool $load
   *   If the returned entities shall be load. If FALSE, array of ids are
   *   returned.
   * @param array $conditionParams
   *   Array of the additional conditions that are passed to the entityQuery.
   *
   * @return array
   *   If load is TRUE array of BorgerdkSelfsevice is returned,
   *   If load is FALSE array of ids is returned,
   *   If field is empty, empty array is returned.
   */
  public function getSelfservices($load = TRUE, array $conditionParams = []);

}
