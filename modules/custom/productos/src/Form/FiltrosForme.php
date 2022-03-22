<?php
/**
 * @file
 * Contains \Drupal\productos\Form\FiltrosForme.
 */
namespace Drupal\productos\Form;

use Drupal\productos\Controller\EventApiController;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Configure custom_rest settings for this site.
 */
class FiltrosForme extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'productos_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['#theme'] = 'productos_form';

    $form['temperature'] = [
      '#title' => $this->t('Temperature'),
      '#type' => 'select',
      '#options' => ['warm' => 'Warm', 'cool' => 'Cool'],
      '#empty_option' => $this->t('-select'),
      '#ajax' => [
      'callback' => '::colorCallback',
      'wrapper' => 'color-wrapper',
      ],
      ];
      // Desactivar la caché de formulario
      $form_state->setCached(FALSE);
      $form['color_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'color-wrapper'],
      ];
      $form['actions'] = [
      '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      ];

    return $form;
  }


    public function colorCallback(array &$form, FormStateInterface $form_state) {
    $temperature = $form_state->getValue('temperature');
    $user_rol = \Drupal::currentUser()->getRoles();
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
      $city = !empty($request['city']) ? $request['city'] : NULL;
      $brand = !empty($request['brand']) ? $request['brand'] : NULL;
    
      $category = !empty($request['category']) ? $request['category'] : NULL;
      $response = EventApiController::productsList($city, $brand, $category);
      

    $form['color_wrapper']['color'] = [
    '#type' => 'select',
    '#title' => $this->t('Color'),
    '#options' =>  ['warm' => $temperature],
    ];
  $form['color_wrapper']['catalogo'] = [
    '#markup' => '<h2>' .  var_dump($response[0]) . '$</h2>',
  ];
    return $form['color_wrapper'];
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }
}