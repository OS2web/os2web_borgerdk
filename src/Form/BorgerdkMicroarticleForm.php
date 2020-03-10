<?php

namespace Drupal\os2web_borgerdk\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os2web_borgerdk\Entity\BorgerdkMicroarticle;

/**
 * Form controller for the borger.dk microarticle entity edit forms.
 */
class BorgerdkMicroarticleForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface $entity */
    $entity = $this->getEntity();

    // Calculates the weight of the new microarticle as the weight of the
    // max + 1.
    if ($entity->isNew() && $entity->getWeight() == 0) {
      /** @var \Drupal\os2web_borgerdk\BorgerdkArticleInterface $article */
      $article = $entity->getArticle();
      // Getting all microarticles from this article, ordered by weight.
      $microarticles = $article->getMicroarticles(FALSE);

      if (!empty($microarticles)) {
        $highestWeightMicroarticle = end($microarticles);
        /** @var \Drupal\os2web_borgerdk\BorgerdkMicroarticleInterface $lastMicroarticle */
        $lastMicroarticle = BorgerdkMicroarticle::load($highestWeightMicroarticle);

        // Getting last microarticle weight.
        $lastWeight = $lastMicroarticle->getWeight();

        // Setting new weight, as last MA weight +1.
        $lastWeight++;
        $entity->setWeight($lastWeight);
      }

      // Setting source.
      $entity->setSource('Manual');
    }

    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New Borger.dk microarticle %label has been created.', $message_arguments));
      $this->logger('os2web_borgerdk')->notice('Created new Borger.dk microarticle %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The Borger.dk microarticle %label has been updated.', $message_arguments));
      $this->logger('os2web_borgerdk')->notice('Updated new Borger.dk microarticle %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.os2web_borgerdk_microarticle.canonical', ['os2web_borgerdk_microarticle' => $entity->id()]);
  }

}
