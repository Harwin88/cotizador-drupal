<?php

namespace Drupal\productos\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Configure AB Social Studio settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  public const SETTINGS_KEY = 'productos.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'productos_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::SETTINGS_KEY];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user_rol = \Drupal::currentUser()->getRoles();
    if ($user_rol[1] == "operaciones") {
      $form['#attached']['library'][] = 'productos/productos';
    }
    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Settings'),
    ];

    $form['conte_input'] = [
      '#type' => 'details',
      '#title' => $this
        ->t('Configuracion api tapit descuentos puntos'),
      '#group' => 'settings',
    ];

    $form['conte_input']['estatus'] = [
      '#type' => 'radios',
      '#title' => t('Estado'),
      '#default_value' => $config->get('estatus'),
      '#options' => [
        '1' => t('Developer'),
        '2' => t('Product'),
      ],
    ];

    $form['conte_input']['dominio_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('dominio dev'),
      '#default_value' => $config->get('dominio_dev'),
    ];

    $form['conte_input']['key_dev'] = [
      '#type' => 'textfield',
      '#title' => $this->t('x-api-appkey dev'),
      '#default_value' => $config->get('key_dev'),
    ];

    $form['conte_input']['dominio_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('dominio prod'),
      '#default_value' => $config->get('dominio_prod'),
    ];

    $form['conte_input']['key_prod'] = [
      '#type' => 'textfield',
      '#title' => $this->t('x-api-appkey prod'),
      '#default_value' => $config->get('key_prod'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ([] as $field_key) {
      $fid = $form_state->getValue([$field_key, 0]);
      if ($fid) {
        $file = File::load($fid);
        $file->setPermanent();
        $file->save();
      }
    }

    $config = $this->configFactory->getEditable(self::SETTINGS_KEY)
      // Social Studio.
      ->set('estatus', $form_state->getValue('estatus'))
      ->set('dominio_dev', $form_state->getValue('dominio_dev'))
      ->set('key_dev', $form_state->getValue('key_dev'))
      ->set('dominio_prod', $form_state->getValue('dominio_prod'))
      ->set('key_prod', $form_state->getValue('key_prod'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
