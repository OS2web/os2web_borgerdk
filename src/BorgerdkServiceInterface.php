<?php

namespace Drupal\os2web_borgerdk;

/**
 * Interface BorgerdkServiceInterface.
 */
interface BorgerdkServiceInterface {

  /**
   * Get a list of available municipalities provided by Borger.dk.
   *
   * @return array
   *   Array of the municipality names mapped by municipality code.
   */
  public function getMunicipalitiesList();

}
