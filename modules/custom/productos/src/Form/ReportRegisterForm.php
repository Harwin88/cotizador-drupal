<?php

namespace Drupal\productos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\productos\Controller\EventApiController;

/**
 * Event request search form.
 */
class ReportRegisterForm extends FormBase {
  /**
   * User registration start date.
   *
   * @var datetime
   */
  public static $registrationStartDateSessionKey = 'productos_registration_start_date';
  /**
   * User registration end date.
   *
   * @var datetime
   */
  public static $registrationEndDateSessionKey = 'productos_registration_end_date';
  /**
   * User name.
   *
   * @var string
   */
  public static $nameSessionKey = 'productos_name';
  /**
   * User last name.
   *
   * @var string
   */
  public static $lastNameSessionKey = 'productos_last_name';
  /**
   * User ID.
   *
   * @var int
   */
  public static $userIDSessionKey = 'productos_user_id';
  /**
   * User email.
   *
   * @var string
   */
  public static $emailSessionKey = 'productos_email';
  /**
   * Brand.
   *
   * @var string
   */
  public static $brandSessionKey = 'productos_brand';
  /**
   * Event name.
   *
   * @var string
   */
  public static $eventNameSessionKey = 'productos_event_name';
  /**
   * Event ID.
   *
   * @var int
   */
  public static $eventIDSessionKey = 'productos_event_id';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_repor';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user_rol = \Drupal::currentUser()->getRoles();
    if ($user_rol[1] == "operaciones") {
      $form['#attached']['library'][] = 'productos/productos';
    }
    $session = \Drupal::request()->getSession();
    $search_start_date = $session->get(ReportRegisterForm::$registrationStartDateSessionKey);
    $search_end_date = $session->get(ReportRegisterForm::$registrationEndDateSessionKey);
    $search_name = trim($session->get(ReportRegisterForm::$nameSessionKey));
    $search_last_name = trim($session->get(ReportRegisterForm::$lastNameSessionKey));
    $search_user_id = trim($session->get(ReportRegisterForm::$userIDSessionKey));
    $search_email = trim($session->get(ReportRegisterForm::$emailSessionKey));
    $search_brand = trim($session->get(ReportRegisterForm::$brandSessionKey));
    $search_event_name = trim($session->get(ReportRegisterForm::$eventNameSessionKey));
    $search_event_id = trim($session->get(ReportRegisterForm::$eventIDSessionKey));

    $form['start_date'] = [
      '#title' => t('Start date'),
      '#type' => 'datetime',
      '#default_value' => $search_start_date ? $search_start_date : NULL,
    ];

    $form['end_date'] = [
      '#title' => t('End date'),
      '#type' => 'datetime',
      '#default_value' => $search_end_date ? $search_end_date : NULL,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => $search_name,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => t('Last name'),
      '#default_value' => $search_last_name,
    ];

    $form['user_id'] = [
      '#type' => 'number',
      '#title' => t('User ID'),
      '#default_value' => $search_user_id,
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#default_value' => $search_email,
    ];

    $form['brand'] = [
      '#type' => 'select',
      '#title' => t('Brand'),
      '#default_value' => $search_brand,
      '#empty_option' => t('Select a brand'),
      '#options' => EventApiController::getTaxonomyData('marcas'),
    ];

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => t('Product name'),
      '#default_value' => $search_event_name,
    ];

    $form['event_id'] = [
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#default_value' => $search_event_id,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filter'),
    ];

    list($header, $rows) = $this->getReportData();

    $form['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#prefix' => '<div class="report-register-table">',
      '#suffix' => '</div>',
      '#empty' => t('No records'),
    ];

    $form['pager'] = [
      '#type' => 'pager',
      '#element' => 0,
    ];

    $form['#cache']['contexts'][] = 'session';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $start_date = $form_state->getValue('start_date');
    $end_date = $form_state->getValue('end_date');
    $name = trim($form_state->getValue('name'));
    $last_name = trim($form_state->getValue('last_name'));
    $user_id = trim($form_state->getValue('user_id'));
    $email = trim($form_state->getValue('email'));
    $brand = trim($form_state->getValue('brand'));
    $event_name = trim($form_state->getValue('event_name'));
    $event_id = trim($form_state->getValue('event_id'));
    $session = \Drupal::request()->getSession();
    $session->set(ReportRegisterForm::$registrationStartDateSessionKey, $start_date);
    $session->set(ReportRegisterForm::$registrationEndDateSessionKey, $end_date);
    $session->set(ReportRegisterForm::$nameSessionKey, $name);
    $session->set(ReportRegisterForm::$lastNameSessionKey, $last_name);
    $session->set(ReportRegisterForm::$emailSessionKey, $email);
    $session->set(ReportRegisterForm::$userIDSessionKey, $user_id);
    $session->set(ReportRegisterForm::$brandSessionKey, $brand);
    $session->set(ReportRegisterForm::$eventNameSessionKey, $event_name);
    $session->set(ReportRegisterForm::$eventIDSessionKey, $event_id);
    $form_state->setRedirect('productos.report.productos.userReportForm');
  }

  /**
   * Function.
   */
  public static function getReportData($excel = FALSE) {
    $session = \Drupal::request()->getSession();
    $search_start_date = $session->get(ReportRegisterForm::$registrationStartDateSessionKey);
    $search_end_date = $session->get(ReportRegisterForm::$registrationEndDateSessionKey);
    $search_name = trim($session->get(ReportRegisterForm::$nameSessionKey));
    $search_last_name = trim($session->get(ReportRegisterForm::$lastNameSessionKey));
    $search_user_id = trim($session->get(ReportRegisterForm::$userIDSessionKey));
    $search_email = trim($session->get(ReportRegisterForm::$emailSessionKey));
    $search_brand = trim($session->get(ReportRegisterForm::$brandSessionKey));
    $search_event_name = trim($session->get(ReportRegisterForm::$eventNameSessionKey));
    $search_event_id = trim($session->get(ReportRegisterForm::$eventIDSessionKey));
    $rows = [];

    $header = [
      'id',
      'register_date',
      'name',
      'last_name',
      'type_document',
      'identification_card',
      'email',
      'phone',
      'department',
      'city',
      'address',
      'additional_address_information',
      'postal_code',
      'name_invited',
      'last_name_invited',
      'type_document_invited',
      'identification_card_invited',
      'email_invited',
      'phone_invited',
      'name_brand',
      'name_event',
      'points_tapit',
      'city',
      'ticket_type',
      'size',
      'color',
    ];

    $query = \Drupal::database()->select('user_for_event', 'us');
    $query->join('productos', 'e', 'e.id_event = us.id_event');
    $query->fields('us');
    $query->fields('e');
    if (!$excel) {
      $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
      $query
        ->limit(20)
        ->element(0);
    }

    if ($search_start_date) {
      $query->condition('us.created', $search_start_date->getTimestamp(), '>=');
    }

    if ($search_end_date) {
      $query->condition('us.created', $search_end_date->getTimestamp(), '<=');
    }

    if ($search_name && !$excel) {
      $query->condition('us.name', '%' . $search_name . '%', 'LIKE');
    }

    if ($search_last_name && !$excel) {
      $query->condition('us.last_name', '%' . $search_last_name . '%', 'LIKE');
    }

    if ($search_user_id && !$excel) {
      $query->condition('us.identification_card', $search_user_id, '=');
    }

    if ($search_email && !$excel) {
      $query->condition('us.email', '%' . $search_email . '%', 'LIKE');
    }

    if ($search_brand) {
      $query->condition('e.name_brand', $search_brand, '=');
    }

    if ($search_event_name) {
      $query->condition('e.name_event', '%' . $search_event_name . '%', 'LIKE');
    }

    if ($search_event_id) {
      $query->condition('e.id_event', $search_event_id, '=');
    }

    $result = $query
      ->orderBy('name', 'ASC')
      ->execute();

    $date_formatter = \Drupal::service('date.formatter');
    foreach ($result as $item) {
      $row = [
        $item->id,
        $date_formatter->format($item->created, 'custom', 'd/m/Y H:i:s'),
        $item->name,
        $item->last_name,
        $item->type_document == 1 ? 'Cédula' : 'Cédula de extranjería',
        $item->identification_card,
        $item->email,
        $item->phone,
        $item->department,
        $item->city,
        $item->address,
        $item->additional_address_information,
        $item->postal_code,
        $item->name_invited,
        $item->last_name_invited,
        $item->type_document_invited == 1 ? 'Cédula' : 'Cédula de extranjería',
        $item->identification_card_invited,
        $item->email_invited,
        $item->phone_invited,
        $item->name_brand,
        $item->name_event,
        $item->points_tapit,
        $item->event_city,
        $item->ticket_type == 1 ? 'Doble' : 'Una persona',
        $item->size,
        $item->color,
      ];
      $rows[] = $row;
    }

    return [$header, $rows];
  }

}
