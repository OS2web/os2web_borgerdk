<?php

namespace Drupal\os2web_borgerdk\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the borger.dk selfservice entity edit forms.
 */
class BorgerdkSelfserviceForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\os2web_borgerdk\BorgerdkSelfserviceInterface $entity */
    $entity = $this->getEntity();

    // Setting source.
    if ($entity->isNew()) {
      $entity->setSource('Manual');
    }

    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New Borger.dk selfservice %label has been created.', $message_arguments));
      $this->logger('os2web_borgerdk')->notice('Created new Borger.dk selfservice %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The Borger.dk selfservice %label has been updated.', $message_arguments));
      $this->logger('os2web_borgerdk')->notice('Updated new Borger.dk selfservice %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.os2web_borgerdk_selfservice.canonical', ['os2web_borgerdk_selfservice' => $entity->id()]);
  }

}
