<?php

namespace Drupal\productos\Form;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\productos\Controller\EventApiController;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
/**
 * Configure example settings for this site.
 */
class UserClientForm extends ConfigFormBase {
  /*
   * @var string Config settings
   */
  const SETTINGS = 'app.core';

  /**
   * Filter.
   *
   * @var string
   */
  public static $filterSessionKey = 'user_create';

  /**
   * Get form Id string.
   */
  public function getFormId() {
    return 'register_dorm';
  }

  /**
   * Create form render.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Function.
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Function.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $form['debug'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['debug-out'],
      ],
    ];

    $form['debug']['email']=[
      '#type' => 'email',
      '#class' => 'form-control',
      "#placeholder"=>"email@dominio.com",
    ];

    $form['debug']['name'] = [
      '#type' => 'textfield',
      "#placeholder"=>"Nombre y apellidos",
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' => '',
      '#class' => 'form-control',
    ];

    $form['debug']['phlonet']=[
      '#type' => 'number',
      "#placeholder"=>"+57 31221222212",
      '#class' => 'form-control',
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    ];


    $form['debug']['intereses']=[
      '#type' => 'hidden',
      '#title' => $this->t('Intereses json'),
      '#class' => 'form-control',
      '#default_value' =>'',
    ];

  
  
   
      $form['debug']['actions'] = [
        '#type' => 'button',
        '#value' => $this->t('Solisitar'),
        '#ajax' => [
          'callback' => '::createuserAjaxCallback', // don't forget :: when calling a class method.
          //'callback' => [$this, 'myAjaxCallback'], //alternative notation
          'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
          'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Verifying entry...'),
          ],
        ]
      ];
  

    return $form;


  }

  /**
   * Function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
   
    parent::validateForm($form, $form_state);
      
    
  }

  /**
   * Public function addmoreCallback_config(array &$form, FormStateInterface $form_state)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = \Drupal::database()->insert('client_user')
    ->fields([
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'phone' => $form_state->getValue('phlonet'),
      'created' => \Drupal::time()->getRequestTime(),
      'updte' => \Drupal::time()->getRequestTime(),
    ])
    ->execute();
    return $form_state;
  }

  public function createuserAjaxCallback(array $form, FormStateInterface $form_state) {
    $id = \Drupal::database()->insert('client_user')
    ->fields([
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'phone' => $form_state->getValue('phlonet'),
      'created' => \Drupal::time()->getRequestTime(),
      'updte' => \Drupal::time()->getRequestTime(),
    ])
    ->execute();

    $response = new AjaxResponse();
    $debugOut =  "<div class='mensajes-registro'>Felicidades ".$form_state->getValue('name').", tu registro fue exitoso, nos pondremos en contacto antes de 24 horas.</div>";
    $response->addCommand(new ReplaceCommand('#debug-out', $debugOut ));

  
    return $response;


  
  }

  /**
   * Function.
   */
  public static function getReportData($excel = FALSE) {
   
  }

  /**
   * To get all cities from taxonomies.
   */
  private function getBrand() {
    $vid = 'marcas';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

  /**
   * To get all cities from taxonomies.
   */
  private function getIdTaxonomy($name_taxonomy, $name) {
    $vid = $name_taxonomy;
    $id_brand = '';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {
      if ($term->name == $name) {
        $id_brand = $term->tid;
        return $id_brand;
      }
    }
    return '';
  }

  /**
   * To get all cities from taxonomies.
   */
  private function getCategory() {
    $vid = 'categorias';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

  /**
   * Add imagen for event.
   */
  public function editEventImage($urlDes, $urlMobile, $id) {
    \Drupal::database()->update('imagen_event')
      ->fields([
        'url_image' => $urlDes,
        'url_image_mobile' => $urlMobile,
        'id_event' => $id,
      ])->condition('id_event', $id, '=')
      ->execute();
  }

  /**
   * Add imagen for event.
   */
  public function addImagenEvent($urlDes, $urlMobile, $id) {
    \Drupal::database()->insert('imagen_event')
      ->fields([
        'url_image' => $urlDes,
        'url_image_mobile' => $urlMobile,
        'id_event' => $id,
      ])
      ->execute();
  }

 

  /**
   * Function.
   */
  public function editMoreCallbackConfig(array &$form, FormStateInterface $form_state) {
    try {
      $fid = $form_state->getValue(['image_upload', 0]);
      $fidMobile = $form_state->getValue(['image_upload_mobile', 0]);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $fileDesk = File::load($fid);
      $fileMobie = File::load($fidMobile);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $urlDes = file_create_url($fileDesk->getFileUri());
      $urlMobile = file_create_url($fileMobie->getFileUri());

      $edi = \Drupal::request()->request->get('id_evento');
      $id = \Drupal::database()->update('productos')
        ->fields([
          'name_event' => $form_state->getValue('name_event'),
          'description' => $form_state->getValue('description'),
          'number_tickets' => $form_state->getValue('number_tickets'),
          'number_tickets_remaining' => $form_state->getValue('number_tickets'),
          'points_tapit' => $form_state->getValue('point_tapit'),
          'event_city' => $form_state->getValue('event_city'),
          'event_address' => $form_state->getValue('event_address'),
          'ticket_type' => $form_state->getValue('ticket_type') == 1 ? 1 : 2,
          'event_date' => $form_state->getValue('event_date'),
          'created' => \Drupal::time()->getRequestTime(),
        ])->condition('id_event', $edi, '=')
        ->execute();

      $this->addImagenEvent($urlDes, $urlMobile, $id);

      return $this->getConfigBloque();
    }
    catch (Exception $e) {
      \Drupal::logger('ABI-INBEV log SSo insert')->info("sso insert config" . print_r($e, 1));
    }
  }

  

}
