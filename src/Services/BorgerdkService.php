<?php

namespace Drupal\os2web_borgerdk\Services;

use BorgerDk\ArticleService\Client;
use BorgerDk\ArticleService\Resources\Endpoints\GetMunicipalityList;
use Drupal\os2web_borgerdk\BorgerdkServiceInterface;

/**
 * OS2Web Borger.dk Service service.
 */
class BorgerdkService implements BorgerdkServiceInterface {

  /**
   * {@inheritDoc}
   */
  public function getMunicipalitiesList() {
    $cid = 'os2web_borgerdk:municipalities_list';

    if ($cache = \Drupal::cache()
      ->get($cid)) {
      $municipalitiesList = $cache->data;
    }
    else {
      $client = new Client();
      $municipalities = new GetMunicipalityList($client);
      $municipalitiesList = $municipalities->getResultFormatted();

      // Caching for 10m = 600 seconds.
      \Drupal::cache()
        ->set($cid, $municipalitiesList, time() + 600);
    }

    return $municipalitiesList;
  }

}
