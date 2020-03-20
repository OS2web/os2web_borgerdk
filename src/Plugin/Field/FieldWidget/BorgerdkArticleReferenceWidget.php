<?php

namespace Drupal\os2web_borgerdk\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os2web_borgerdk\Entity\BorgerdkArticle;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "os2web_borgerdk_article_reference_widget",
 *   module = "os2web_borgerdk",
 *   label = @Translation("Borger.dk article reference widget"),
 *   field_types = {
 *     "os2web_borgerdk_article_reference"
 *   }
 * )
 */
class BorgerdkArticleReferenceWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\os2web_borgerdk\Plugin\Field\FieldType\BorgerdkArticleReference $articleReferenceItem */
    $articleReferenceItem = $items[$delta];

    $fieldName = $articleReferenceItem->getFieldDefinition()->getName();

    // Getting currently selected article, form state value, or loaded value if
    // form state is empty.
    $selectedArticle = $articleReferenceItem->getArticleValue(TRUE);
    $selectedMicroarticles = $articleReferenceItem->getMicroarticleIdsValue();
    $selectedSelfservices = $articleReferenceItem->getSelfserviceIdsValue();

    if (!$form_state->isValueEmpty($fieldName)) {
      if (!empty($form_state->getValue($fieldName)[$delta]['target_id'])) {
        $selectedArticleId = $form_state->getValue($fieldName)[$delta]['target_id'][0]['target_id'];
        $selectedArticle = BorgerdkArticle::load($selectedArticleId);
      }
      else {
        $selectedArticle = NULL;
      }
    }

    // Adding select field.
    $select = $element;
    $select = parent::formElement($items, $delta, $select, $form, $form_state);

    $wrapperId = implode('-', $element['#field_parents']) . "$fieldName-container-$delta";
    $articleOptions = $this->getOptions($items->getEntity());
    sort($articleOptions);

    $select += [
      '#type' => 'select',
      '#options' => $articleOptions,
      '#default_value' => $articleReferenceItem->getArticleValue(),
      '#ajax' => [
        'callback' => [$this, 'reloadArticleContent'],
        'wrapper' => $wrapperId,
      ],
    ];
    $select['#weight'] = 0;

    $element += [
      '#type' => 'details',
    ];
    $element['target_id'] = $select;
    $element['#attributes']['id'] = $wrapperId;

    if ($selectedArticle) {
      $element['#title'] = $selectedArticle->label();
    }

    // Adding select microarticles.
    $microarticlesHeader = [
      'title' => $this
        ->t('Microarticle Title'),
      'content_sample' => $this
        ->t('Content sample'),
      'selfservice_count' => $this
        ->t('Selfservices #'),
      'author' => $this
        ->t('Author'),
    ];
    $element['microarticle_ids'] = array(
      '#type' => 'tableselect',
      '#header' => $microarticlesHeader,
      '#options' => ($selectedArticle) ? $this->generateMicroarticleOptions($selectedArticle) : [],
      '#default_value' => ($selectedMicroarticles) ? $selectedMicroarticles : [],
      '#empty' => $this
        ->t('No microarticles found'),
      '#weight' => 1,
    );

    // Adding select selfservices.
    $selfservicesHeader = [
      'title' => $this
        ->t('Selfservice Title'),
      'url' => $this
        ->t('URL'),
      'author' => $this
        ->t('Author'),
    ];
    $element['selfservice_ids'] = array(
      '#type' => 'tableselect',
      '#header' => $selfservicesHeader,
      '#options' => ($selectedArticle) ? $this->generateSelfServiceOptions($selectedArticle) : [],
      '#default_value' => ($selectedSelfservices) ? $selectedSelfservices : [],
      '#empty' => $this
        ->t('No selfservices found'),
      '#weight' => 2,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if (isset($value['target_id'][0])) {
        $values[$delta]['target_id'] = $value['target_id'][0]['target_id'];
        $values[$delta]['microarticle_ids'] = serialize($value['microarticle_ids']);
        $values[$delta]['selfservice_ids'] = serialize($value['selfservice_ids']);
      }
      else {
        unset($values[$delta]);
      }
    }

    return parent::massageFormValues($values, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->multiple) {
      // Multiple select: add a 'none' option for all fields.
      return t('- None -');
    }
    else {
      // Single select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return t('- None -');
      }
    }
  }

  /**
   * Generates list of microarticles options.
   *
   * @param \Drupal\os2web_borgerdk\Entity\BorgerdkArticle $article
   *   Borger.dk article which is used as a source for the microarticles.
   *
   * @return array
   *   Array ready to be used in tableselect.
   */
  protected function generateMicroarticleOptions(BorgerdkArticle $article) {
    $options = [];

    $microarticles = $article->getMicroarticles();
    if (!empty($microarticles)) {
      foreach ($microarticles as $microarticle) {
        $options[$microarticle->id()] = [
          'title' => $microarticle->label(),
          'content_sample' => mb_substr($microarticle->getContent(), 0, 100) . '...',
          'selfservice_count' => count($microarticle->getSelfservices(FALSE)),
          'author' => $microarticle->getOwner()->label(),
        ];
      }
    }

    return $options;
  }

  /**
   * Generates list of selfservice options.
   *
   * @param \Drupal\os2web_borgerdk\Entity\BorgerdkArticle $article
   *   Borger.dk article which is used as a source for the selfservices.
   *
   * @return array
   *   Array ready to be used in tableselect.
   */
  protected function generateSelfServiceOptions(BorgerdkArticle $article) {
    $options = [];

    $selfservices = $article->getSelfservices();
    if (!empty($selfservices)) {
      foreach ($selfservices as $selfservice) {
        $options[$selfservice->id()] = [
          'title' => $selfservice->label(),
          'url' => $selfservice->getUrl(),
          'author' => $selfservice->getOwner()->label(),
        ];
      }
    }

    return $options;
  }

  /**
   * Reloads content of the Borger.dk Article according to the new selection.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   Updated form element of type details.
   */
  public function reloadArticleContent(array $form, FormStateInterface $form_state) {
    // Getting trigger element.
    $triggerElement = $form_state->getTriggeringElement();

    // Getting element parents.
    $arrayParents = $triggerElement['#array_parents'];
    // Removing last element (target_id).
    array_pop($arrayParents);

    // The details element, which is a parent of a triggering element.
    $detailsElements = NestedArray::getValue($form, $arrayParents);

    // Making details element expanded and hiding the weight field.
    $detailsElements['#open'] = TRUE;
    unset($detailsElements['_weight']);

    // Returning the container, which is a widget with a correct delta of a
    // parent element.
    return $detailsElements;
  }

}
