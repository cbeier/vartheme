<?php

/**
 * @file
 * theme-settings.php
 *
 * Provides theme settings for vartheme.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function vartheme_form_system_theme_settings_alter(&$form, FormStateInterface $form_state, $form_id = NULL) {
  $build_info = $form_state->getBuildInfo();
  $theme = isset($build_info['args'][0]) ? $build_info['args'][0] : 'vartheme';
  $theme_config = \Drupal::config($theme . '.settings');

  // Vertical tabs.
  $form['vartheme'] = [
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Vartheme Settings') . '</small></h2>',
    '#weight' => -20,
  ];

  // General Vertical Tab For vartheme Settings.
  $form['vartheme_general'] = [
    '#type' => 'details',
    '#title' => t('General'),
    '#group' => 'vartheme',
  ];

  $form['vartheme_general']['header'] = [
    '#type' => 'details',
    '#title' => t('Header'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];

  // Adding check-box header fluid container.
  $form['vartheme_general']['header']['header_container'] = [
    '#type' => 'checkbox',
    '#title' => t('Header Fluid Container'),
    '#description' => t('Use <code>.container-fluid</code> class instead of <code>.container</code> for the Header region.<br />See: :vartheme_link', [
      ':vartheme_link' => Link::fromTextAndUrl('Fluid container', Url::fromUri('http://getbootstrap.com/css/', ['absolute' => TRUE, 'fragment' => 'grid-example-fluid']))->toString(),
    ]),
    '#default_value' => $theme_config->get('header_container'),
  ];

  // Email logo settings to be used with Varbase Email module.
  $form['email_logo'] = [
    '#type'     => 'details',
    '#title'    => t('Email Logo'),
    '#open' => FALSE,
  ];

  $form['email_logo']['email_logo_default'] = [
    "#type" => "checkbox",
    '#title'    => t('Use the logo supplied by the theme'),
    "#default_value" => $theme_config->get('email_logo_default'),
  ];

  $form['email_logo']['email_logo_settings'] = [
    "#type" => "container",
    '#states' => [
      "invisible" => [
        'input[name="email_logo_default"]' => [
          "checked" => TRUE,
        ],
      ],
    ],
  ];

  $form['email_logo']['email_logo_settings']["email_logo_path"] = [
    "#type" => "textfield",
    "#title" => "Path to custom logo",
    "#default_value" => $theme_config->get('email_logo_path'),
    "#description" => t("Examples: <code>@external-file</code>", ["@external-file" => "http://www.example.com/logo.png"]),
  ];

  $form['email_logo']['email_logo_settings']["email_logo_upload"] = [
    '#type'     => 'managed_file',
    "#title"    => t("Upload logo image"),
    "#description" => t("If you don't have direct file access to the server, use this field to upload your logo."),
    '#required' => FALSE,
    '#upload_location' => \Drupal::config('system.file')->get('default_scheme') . '://theme/email_logo/',
    '#default_value' => $theme_config->get('email_logo_upload'),
    '#upload_validators' => [
      function ($file) {
        $container = \Drupal::getContainer();
        if ($container->has('file.validator')) {
          $validator = $container->get('file.validator');
          $violations = $validator->validate($file, ['FileExtension' => ['extensions' => 'gif png jpg jpeg']]);
          $messages = [];
          foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
          }
          return $messages;
        }
        // Fallback for Drupal < 10.2 (file_validate_extensions removed in Drupal 11).
        return file_validate_extensions($file, 'gif png jpg jpeg');
      },
    ],
  ];
}
