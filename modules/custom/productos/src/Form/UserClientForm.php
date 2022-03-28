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
use Drupal\Core\Ajax\OpenModalDialogCommand;

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
  
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>'
    ];


    $form['email']=[
      '#type' => 'email',
      '#class' => 'form-control',
      "#placeholder"=>"email@dominio.com",
    ];

    $form['name'] = [
      '#type' => 'textfield',
      "#placeholder"=>"Nombre y apellidos",
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' => '',
      '#class' => 'form-control',
    ];

    $form['phlonet']=[
      '#type' => 'number',
      "#placeholder"=>"+57 31221222212",
      '#class' => 'form-control',
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    ];


    $form['intereses']=[
      '#type' => 'hidden',
      '#title' => $this->t('Intereses json'),
      '#class' => 'form-control',
      '#default_value' =>'',
    ];

  
  
   
      $form['actions'] = [
        '#type' => 'button',
        '#value' => $this->t('Solisitar'),
        '#prefix' => '<div class="text-center">',
        '#suffix' => '</div>',
        '#ajax' => [
          'callback' => '::myAjaxCallback',
        ],
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
    
 
    $field = $form_state->getValues();
	
   
	$fields["fname"] = $field['fname'];
	$fields["name"] = $field['name'];
	$fields["age"] = $field['age'];
	$fields["marks"] = $field['marks'];
	    // Display result.
      foreach ($form_state->getValues() as $key => $value) {
        \Drupal::messenger()->addStatus($key . ': ' . $value);
      }
      
      \Drupal::messenger()->addMessage($this->t('Student data=fname='.$fields["fname"].'=name='.$fields["name"].'=age='.$fields["age"].'marks==='.$fields["marks"]));
      
   return $form_state;

    
  }

  public function myAjaxCallback(array $form, FormStateInterface $form_state) {

    $field = $form_state->getValues();
    $fields["email"] = $field['email'];
    $fields["name"] = $field['name'];
    $fields["phlonet"] = $field['phlonet'];
    $fields["marks"] = $field['marks'];
       // Try to get the selected text from the select element on our form.
  $selectedText = 'nothing selected';
 
    $selectedText = $form['email']['#options'][$selectedValue];
  

  // Create a new textfield element containing the selected text.
  // We're replacing the original textfield using an AJAX replace command which
  // expects either a render array or plain HTML markup.
  $elem = [
    '#type' => 'textfield',
    '#size' => '60',
    '#disabled' => TRUE,
    '#value' => "I am a new textfield: $selectedText!",
    '#attributes' => [
      'id' => ['edit-output'],
    ],
  ];

  // Attach the javascript library for the dialog box command
  // in the same way you would attach your custom JS scripts.
  $dialogText['#attached']['library'][] = 'core/drupal.dialog.ajax';
  // Prepare the text for our dialogbox.
  $dialogText['#markup'] = "You selected: $selectedText";

  // If we want to execute AJAX commands our callback needs to return
  // an AjaxResponse object. let's create it and add our commands.
  $response = new AjaxResponse();
  // Issue a command that replaces the element #edit-output
  // with the rendered markup of the field created above.
  // ReplaceCommand() will take care of rendering our text field into HTML.
  $response->addCommand(new ReplaceCommand('#edit-output', $elem));
  $markup = 'nothing selected';

  // Prepare our textfield. Check if the example select field has a selected option.
  if ($form_state->getValue('emil')) {
      // Get the text of the selected option.
      $selectedText = $form['email']['#options'][$selectedValue];
      // Place the text of the selected option in our textfield.
      $markup = "<h1>$selectedText</h1>";
  }

  // Don't forget to wrap your markup in a div with the #edit-output id
  // or the callback won't be able to find this target when it's called
  // more than once.
  $output = "<div id='edit-output'>$markup</div>";

  // Return the HTML markup we built above in a render array.
  return ['#markup' => $output];
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
