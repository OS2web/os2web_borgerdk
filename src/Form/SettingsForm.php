<?php

namespace Drupal\os2web_borgerdk\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure os2web_borgerdk settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Name of the config.
   *
   * @var string
   */
  public static $configName = 'os2web_borgerdk.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2web_borgerdk_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [SettingsForm::$configName];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['sync_detail'] = [
      '#type' => 'details',
      '#title' => t('Sync settings'),
      '#open' => TRUE,
    ];

    /** @var \Drupal\os2web_borgerdk\BorgerdkServiceInterface $service */
    $service = \Drupal::service('os2web_borgerdk.service');

    $form['sync_detail']['selected_municipality'] = [
      '#type' => 'select',
      '#title' => t('Fetch content for selected municipality'),
      '#options' => $service->getMunicipalitiesList(),
      '#required' => FALSE,
      '#empty_value' => 0,
      '#description' => t('Select "- None -" to not have the specific municipality content'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('selected_municipality'),
    ];

    $form['sync_detail']['import_sources'] = [
      '#type' => 'checkboxes',
      '#title' => t('Fetch content from the selected sources'),
      '#options' => [
        'da' => 'Borger.dk',
        'en' => 'Lifeindenmark.borger.dk',
      ],
      '#required' => TRUE,
      '#description' => t('Only content from these sources will be imported'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('import_sources'),
    ];

    // Obsolete articles notification.
    $form['notification_details'] = [
      '#type' => 'details',
      '#title' => t('Obsolete articles notification settings'),
    ];
    $form['notification_details']['obsolete_notification_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Send notification about obsolete Borger.dk articles'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('obsolete_notification_enabled'),
    ];

    $form['notification_details']['obsolete_notification_recipients'] = [
      '#type' => 'textfield',
      '#title' => t('Recipient(s) of the email'),
      '#description' => t('Example: email@example.com, anotheremail@test.com'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('obsolete_notification_recipients'),
      '#states' => [
        'visible' => [
          ':input[name="obsolete_notification_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['notification_details']['obsolete_notification_email_subject'] = [
      '#type' => 'textfield',
      '#title' => t('Email subject'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('obsolete_notification_email_subject'),
      '#states' => [
        'visible' => [
          ':input[name="obsolete_notification_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['notification_details']['obsolete_reminder_email_body'] = [
      '#type' => 'textarea',
      '#title' => t('Email body'),
      '#default_value' => $this->config(SettingsForm::$configName)->get('obsolete_notification_email_body'),
      '#states' => [
        'visible' => [
          ':input[name="obsolete_notification_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['notification_details']['obsolete_notification_email_tokens'] = [
      '#title' => t('Available variables'),
      '#type' => 'details',
      '#states' => [
        'visible' => [
          ':input[name="obsolete_notification_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['notification_details']['obsolete_notification_email_tokens'][] = [
      '#markup' => '<p>' . t('Can be used both in subject and body:') . '</p>
      <ul>
        <li><b>!article_title</b> - ' . t('title of the article') . '
        <li><b>!entities</b> - ' . t('list of content referencing this article') . '
      </ul>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(SettingsForm::$configName)
      ->set('selected_municipality', $form_state->getValue('selected_municipality'))
      ->set('import_sources', $form_state->getValue('import_sources'))
      ->set('obsolete_notification_enabled', $form_state->getValue('obsolete_notification_enabled'))
      ->set('obsolete_notification_recipients', $form_state->getValue('obsolete_notification_recipients'))
      ->set('obsolete_notification_email_subject', $form_state->getValue('obsolete_notification_email_subject'))
      ->set('obsolete_notification_email_body', $form_state->getValue('obsolete_notification_email_body'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
