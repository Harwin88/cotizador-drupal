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

/**
 * Configure example settings for this site.
 */
class ProductosForm extends ConfigFormBase {
  /*
   * @var string Config settings
   */
  const SETTINGS = 'app.core';

  /**
   * Filter.
   *
   * @var string
   */
  public static $filterSessionKey = 'productos_filter';

  /**
   * Get form Id string.
   */
  public function getFormId() {
    return 'app_config_tabs';
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
    $session = \Drupal::request()->getSession();
    $search_filter = $session->get(ProductosForm::$filterSessionKey);
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $edi = !empty($request['id_evento']) ? $request['id_evento'] : NULL;
    $delete = !empty($request['delete_id']) ? $request['delete_id'] : NULL;
    $respuesta = [];
    $user_rol = [];
    // $id = 0;
    $user_rol = \Drupal::currentUser()->getRoles();
    $id = isset($edi) ? $edi : -1;

    if ($user_rol[1] == "operaciones") {
      $form['#attached']['library'][] = 'productos/productos';
    }

    if ($id == -1) {
      $id = isset($delete) ? $delete : -1;
    }
    else {
      $respuesta = $this->getEvent($id);
    }

    if ($id != -1) {
      $respuesta = $this->getEvent($id);
    }

    $form['info'] = [
      '#type' => 'details',
      '#title' => t('PRODUCT CREATE'),
      '#open' => FALSE,
    ];

    $form['info']['name_event'] = [
      '#title' => t('Product name'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->name_event) ? $respuesta[0]->name_event : '',
      '#disabled' => FALSE,
    ];

    $form['info']['url_landing_promo'] = [
      '#title' => t('URL earn points'),
      '#default_value' => isset($respuesta[0]->url_landing_promo) ? $respuesta[0]->url_landing_promo : '',
      '#type' => 'textfield',
    ];

    $form['info']['point_tapit'] = [
      '#title' => t('Precio Unidad'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->points_tapit) ? $respuesta[0]->points_tapit : '',
      '#disabled' => FALSE,
    ];

    $form['info']['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => isset($respuesta[0]->description) ? $respuesta[0]->description : '',
      '#disabled' => FALSE,
    ];

    $form['info']['number_tickets'] = [
      '#title' => t('Stock'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->number_tickets) ? $respuesta[0]->number_tickets : '',
      '#disabled' => FALSE,
    ];

    if (isset($edi)) {
      $form['info']['number_tickets_remaining'] = [
        '#title' => t('Stock available'),
        '#type' => 'number',
        '#default_value' => isset($respuesta[0]->number_tickets_remaining) ? $respuesta[0]->number_tickets_remaining : '',
        '#disabled' => FALSE,
      ];
    }

    $form['info']['ticket_type'] = [
      '#type' => 'radios',
      '#title' => t('Ticket type'),
      '#default_value' => isset($respuesta[0]->ticket_type) ? $respuesta[0]->ticket_type : 1,
      '#options' => [
        '1' => t('Double'),
        '2' => t('Personal'),
      ],
    ];

    $form['info']['category'] = [
      '#title' => t('Category'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('category', !empty($respuesta[0]->category) ? $respuesta[0]->category : ''),
      '#empty_option' => t('Select a category'),
      '#options' => self::getCategory(),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['dates']['event_date'] = [
      '#title' => t('Date'),
      '#type' => 'datetime',
      '#disabled' => FALSE,
      '#default_value' => isset($respuesta[0]->event_date) ? new DrupalDateTime($respuesta[0]->event_date) : NULL,
    ];

    $form['info']['event_city'] = [
      '#title' => t('Availability city'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->event_city) ? $respuesta[0]->event_city : '',
      '#disabled' => FALSE,
    ];

    if (isset($edi)) {
      $form['info']['event_status'] = [
        '#type' => 'checkbox',
        '#title' => t('Deactivate?'),
        '#description' => t('To activate/deactivate '),
        '#default_value' => isset($respuesta[0]->event_status) != '' ? ($respuesta[0]->event_status == 0 ? 1 : 0) : '',
      ];
    }

    $form['info']['name_brand'] = [
      '#title' => t('Brand'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('marcas', !empty($respuesta[0]->name_brand) ? $respuesta[0]->name_brand : ''),
      '#empty_option' => t('Select a brand'),
      '#options' => self::getBrand(),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['event_address'] = [
      '#title' => t('Canje address'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->event_address) ? $respuesta[0]->event_address : '',
      '#disabled' => FALSE,
    ];

    $form['info']['origin_type'] = [
      '#title' => t('Canje origin'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->origin_type) ? $respuesta[0]->origin_type : '',
      '#disabled' => FALSE,
    ];

    $validators = [
      'file_validate_extensions' => ['jpg jpeg png'],
      // 10 MB limit.
      'file_validate_size' => [10 * 1024 * 1024],
    ];

    $form['info']['image_upload'] = [
      '#type' => 'managed_file',
      '#title' => t('Desktop image'),
      '#size' => 20,
      '#description' => t("Limit size") . ' 10 MB. ' . t('Allowed extensions') . ': jpg, png',
      '#upload_validators' => $validators,
      '#upload_location' => 'public://ab_event/event/all/',
      '#attributes' => [
        'class' => ['inputfile'],
        'accept' => ['image/x-png,image/jpeg'],
        'class' => ['abi-file_control'],
      ],
      '#default_value' => !empty($edi) ? [EventApiController::getImageId($edi, 'd')] : NULL,
    ];

    if (!empty($edi) && EventApiController::getImageId($edi, 'd') == 0) {
      $image_url = EventApiController::getImageData($edi, 'd');
      $form['info']['image_upload_other'] = [
        "#type" => "item",
        "#markup" => '<a href="' . $image_url . '" target="_blank"><img src="' . $image_url . '" width="200px" height="200px"></a>',
      ];
    }

    $form['info']['image_upload_mobile'] = [
      '#type' => 'managed_file',
      '#title' => t('Mobile Image'),
      '#size' => 15,
      '#description' => t("Limit size") . ' 8 MB. ' . t('Allowed extensions') . ': jpg, png',
      '#upload_validators' => $validators,
      '#upload_location' => 'public://ab_event/event/all/',
      '#attributes' => [
        'class' => ['inputfile'],
        'accept' => ['image/x-png,image/jpeg'],
        'class' => ['abi-file_control'],
      ],
      '#default_value' => !empty($edi) ? [EventApiController::getImageId($edi, 'm')] : NULL,
    ];

    if (!empty($edi) && EventApiController::getImageId($edi, 'm') == 0) {
      $image_url = EventApiController::getImageData($edi, 'm');
      $form['info']['image_upload_mobile_other'] = [
        "#type" => "item",
        "#markup" => '<a href="' . $image_url . '" target="_blank"><img src="' . $image_url . '" width="200px" height="200px"></a>',
      ];
    }

    $form['info']['url_tyc'] = [
      '#title' => t('Url terms and conditions'),
      '#default_value' => isset($respuesta[0]->url_tyc) ? $respuesta[0]->url_tyc : '',
      '#type' => 'textfield',
    ];

    $form['info']['require_location'] = [
      '#type' => 'checkbox',
      '#title' => t('Require location?'),
      '#description' => t('To collect location data'),
      '#default_value' => isset($respuesta[0]->require_location) ? $respuesta[0]->require_location : '',
    ];

    $form['info']['require_redirect'] = [
      '#type' => 'checkbox',
      '#title' => t('Require redirect?'),
      '#description' => t('Define redirection to url_landing_promo'),
      '#default_value' => isset($respuesta[0]->require_redirect) ? $respuesta[0]->require_redirect : '',
    ];

    $form['info']['size'] = [
      '#title' => t('Product size'),
      '#default_value' => isset($respuesta[0]->size) ? $respuesta[0]->size : '',
      '#maxlength' => 50,
      '#type' => 'textfield',
    ];

    $form['info']['color'] = [
      '#title' => t('Product color'),
      '#default_value' => isset($respuesta[0]->color) ? $respuesta[0]->color : '',
      '#maxlength' => 50,
      '#type' => 'textfield',
    ];

    $form['info']['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Hide canje?'),
      '#description' => t('To hide/show the canje'),
      '#default_value' => isset($respuesta[0]->status) != '' ? ($respuesta[0]->status == 0 ? 1 : 0) : '',
    ];

    if (isset($edi)) {
      $form['info']['boton-agregar'][] = [
        'add_option_row' => [
          '#wrapper_attributes' => [
            'colspan' => '3',
            'class' => [
              'container-inline',
            ],
          ],
          '#tree' => FALSE,

          // Works with ajax as supposed to.
          'button_add' => [
            '#type' => 'button',
            '#name' => 'add_preset_option_confgiw',
            '#value' => $this->t('Edit'),
            '#type' => 'submit',
          ],
        ],
      ];
    }
    else {
      if (!isset($delete)) {
        $form['info']['boton-agregar'][] = [
          'add_option_row' => [
            '#wrapper_attributes' => [
              'colspan' => '3',
              'class' => [
                'container-inline',
              ],
            ],
            '#tree' => FALSE,

            // Works with ajax as supposed to.
            'button_add' => [
              '#type' => 'submit',
              '#name' => 'add_preset_option_confgiw',
              '#value' => $this->t('Create'),
              // '#ajax' => array(
              // 'callback' =>  '::addmoreCallback_config',
              // 'event' => 'click',
              // 'wrapper' => 'preset-wrapper-config',
              // ),
            ],
          ],
        ];
      }
    }

    if (isset($delete)) {
      $form['info']['boton-agregar'][] = [
        'add_option_row' => [
          '#wrapper_attributes' => [
            'colspan' => '3',
            'class' => [
              'container-inline',
            ],
          ],
          '#tree' => FALSE,

          // Works with ajax as supposed to.
          'button_add' => [
            '#type' => 'button',
            '#name' => 'add_preset_option_confgiw',
            '#value' => $this->t('Delete'),
            '#ajax' => [
              'callback' => '::deletetarmoreCallbackConfig',
              'event' => 'click',
              'wrapper' => 'preset-wrapper-config',
            ],
          ],
        ],
      ];

      $form['info']['boton-add-config'][] = [
        'add_option_row' => [
          '#wrapper_attributes' => [
            'colspan' => '3',
            'class' => [
              'container-inline',
            ],
          ],
          '#tree' => FALSE,

          // Works with ajax as supposed to.
          'button_add' => [
            '#type' => 'submit',
            '#name' => 'add_preset_option_confgiw',
            '#value' => $this->t('Create'),
            // '#ajax' => array(
            // 'callback' =>  '::addmoreCallback_config',
            // 'event' => 'click',
            // 'wrapper' => 'preset-wrapper-config',
            // ),
          ],
        ],
      ];
    }

    $form['report'] = [
      '#type' => 'details',
      '#title' => t('PRODUCTS REPORT'),
      '#open' => TRUE,
    ];

    list($header, $rows) = $this->getReportData();

    $form['report']['filter'] = [
      '#type' => 'textfield',
      '#title' => t('Search'),
      '#description' => t('You can search by: <b>Product name</b>, <b>Product city</b>, <b>Sponsor brand</b>'),
      '#maxlength' => 100,
      '#default_value' => $search_filter,
    ];

    $form['report']['submit_filter'] = [
      '#type' => 'submit',
      '#value' => t('Filter'),
    ];

    $form['report']['clear_filter'] = [
      '#type' => 'submit',
      '#value' => t('Clear filter'),
    ];

    $form['report']['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#prefix' => '<div class="report-products-table">',
      '#suffix' => '</div>',
      '#empty' => t('No records'),
    ];

    $form['report']['pager'] = [
      '#type' => 'pager',
      '#element' => 0,
    ];

    $form['#cache']['contexts'][] = 'session';

    return $form;
  }

  /**
   * Function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $btn_action = !empty($form_state->getValues()['op']) ? strval($form_state->getValues()['op']) : '';
    if ($btn_action != t('Filter') && $btn_action != ('Clear filter')) {
      $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
      $edi_eve = !empty($request['id_evento']) ? $request['id_evento'] : NULL;
      $delete = !empty($request['delete_id']) ? $request['delete_id'] : NULL;

      if (!isset($edi_eve)) {
        if ($form_state->getValue('category') == '') {
          $form_state->setErrorByName('category', $this->t('Field Category is required'));
        }
        if ($form_state->getValue('name_brand') == '') {
          $form_state->setErrorByName('name_brand', $this->t('Field Brand is required'));
        }
        if (empty($form_state->getValue(['image_upload', 0]))) {
          $form_state->setErrorByName('image_upload', $this->t('Field Desktop image is required'));
        }
        if (empty($form_state->getValue(['image_upload_mobile', 0]))) {
          $form_state->setErrorByName('image_upload_mobile', $this->t('Field Mobile image is required'));
        }
        if ($form_state->getValue('name_event') == '') {
          $form_state->setErrorByName('name_event', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('url_landing_promo') == '') {
          $form_state->setErrorByName('url_landing_promo', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('description') == '') {
          $form_state->setErrorByName('description', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('number_tickets') == '') {
          $form_state->setErrorByName('number_tickets', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('point_tapit') == '') {
          $form_state->setErrorByName('point_tapit', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('event_city') == '') {
          $form_state->setErrorByName('event_city', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('name_brand') == '') {
          $form_state->setErrorByName('name_brand', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('category') == '') {
          $form_state->setErrorByName('category', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('origin_type') == '') {
          $form_state->setErrorByName('origin_type', $this->t('Ingrese un valor.'));
        }

        return $form_state;
      }
    }
  }

  /**
   * Public function addmoreCallback_config(array &$form, FormStateInterface $form_state)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $btn_action = strval($form_state->getValues()['op']);
    if ($btn_action == t('Filter')) {
      $filter = trim($form_state->getValue('filter'));
      $session = \Drupal::request()->getSession();
      $session->set(ProductosForm::$filterSessionKey, $filter);
      $form_state->setRedirect('productos.admin.productos.config');
    }
    elseif ($btn_action == t('Clear filter')) {
      $session = \Drupal::request()->getSession();
      $session->set(ProductosForm::$filterSessionKey, '');
    }
    else {
      try {
        $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
        $edi_eve = !empty($request['id_evento']) ? $request['id_evento'] : NULL;
        $delete = !empty($request['delete_id']) ? $request['delete_id'] : NULL;
        $brand = Term::load($form_state->getValue('name_brand'))->get('name')->value;
        $category = Term::load($form_state->getValue('category'))->get('name')->value;

        $fid = $form_state->getValue(['image_upload', 0]);
        $fidMobile = $form_state->getValue(['image_upload_mobile', 0]);
        $fileDesk = File::load($fid);
        $fileMobie = File::load($fidMobile);
        $urlDes = $fileDesk->getFileUri();
        $urlMobile = $fileMobie->getFileUri();

        if ($edi_eve) {
          $id = \Drupal::database()->update('productos')
            ->fields([
              'name_event' => $form_state->getValue('name_event'),
              'description' => $form_state->getValue('description'),
              'number_tickets' => $form_state->getValue('number_tickets'),
              'number_tickets_remaining' => $form_state->getValue('number_tickets_remaining'),
              'points_tapit' => $form_state->getValue('point_tapit'),
              'event_city' => $form_state->getValue('event_city'),
              'category' => $category,
              'origin_type' => $form_state->getValue('origin_type'),
              'name_brand' => $brand,
              'url_landing_promo' => $form_state->getValue('url_landing_promo'),
              'event_address' => $form_state->getValue('event_address'),
              'ticket_type' => $form_state->getValue('ticket_type') == 1 ? 1 : 2,
              'event_date' => $form_state->getValue('event_date'),
              'event_status' => $form_state->getValue('event_status') == 0 ? 1 : 0,
              'url_tyc' => $form_state->getValue('url_tyc'),
              'require_location' => $form_state->getValue('require_location'),
              'require_redirect' => $form_state->getValue('require_redirect'),
              'size' => $form_state->getValue('size'),
              'color' => $form_state->getValue('color'),
              'created' => \Drupal::time()->getRequestTime(),
              'status' => $form_state->getValue('status') == 0 ? 1 : 0,
            ])->condition('id_event', $edi_eve, '=')
            ->execute();

          $this->editEventImage($urlDes, $urlMobile, $edi_eve);

          return $form;
        }

        $fileDesk->setPermanent();
        $fileDesk->save();
        $fileMobie->setPermanent();
        $fileMobie->save();

        $id = \Drupal::database()->insert('productos')
          ->fields([
            'name_event' => $form_state->getValue('name_event'),
            'description' => $form_state->getValue('description'),
            'number_tickets' => $form_state->getValue('number_tickets'),
            'number_tickets_remaining' => $form_state->getValue('number_tickets'),
            'points_tapit' => $form_state->getValue('point_tapit'),
            'event_city' => $form_state->getValue('event_city'),
            'category' => $category,
            'name_brand' => $brand,
            'origin_type' => $form_state->getValue('origin_type'),
            'url_landing_promo' => $form_state->getValue('url_landing_promo'),
            'event_address' => $form_state->getValue('event_address'),
            'ticket_type' => $form_state->getValue('ticket_type') == 1 ? 1 : 2,
            'event_date' => $form_state->getValue('event_date'),
            'event_status' => $form_state->getValue('event_status') == 0 ? 1 : 0,
            'url_tyc' => $form_state->getValue('url_tyc'),
            'require_location' => $form_state->getValue('require_location'),
            'require_redirect' => $form_state->getValue('require_redirect'),
            'size' => $form_state->getValue('size'),
            'color' => $form_state->getValue('color'),
            'created' => \Drupal::time()->getRequestTime(),
            'status' => $form_state->getValue('status') == 0 ? 1 : 0,
          ])
          ->execute();

        $this->addImagenEvent($urlDes, $urlMobile, $id);

        return $form;
      }
      catch (Exception $e) {
        \Drupal::logger('ABI-INBEV event insert')->info("event insert config" . print_r($e, 1));
      }
    }
  }

  /**
   * Function.
   */
  public static function getReportData($excel = FALSE) {
    $session = \Drupal::request()->getSession();
    $search_filter = trim($session->get(ProductosForm::$filterSessionKey));

    $rows = [];
    $header = [
      t('Product name'),
      t('Description'),
      t('Tickets number'),
      t('Tapit points'),
      t('Availability city'),
      t('Sponsor brand'),
      t('Tickets type'),
      t('Date'),
      t('Deactivate?'),
      t('Edit'),
    ];

    $query = \Drupal::database()->select('productos', 'pt');
    $query->fields('pt', [
      'id_event',
      'name_event',
      'description',
      'number_tickets', 'points_tapit',
      'event_city',
      'name_brand',
      'ticket_type',
      'event_date',
      'created',
    ]);
    if (!$excel) {
      $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
      $query
        ->limit(20)
        ->element(0);
    }

    if ($search_filter) {
      $or = $query->orConditionGroup();
      $or
        ->condition('name_event', '%' . $search_filter . '%', 'LIKE')
        ->condition('event_city', '%' . $search_filter . '%', 'LIKE')
        ->condition('name_brand', '%' . $search_filter . '%', 'LIKE');
      $query->condition($or);
    }

    $result = $query
      ->orderBy('name_event', 'ASC')
      ->execute();

    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $page = !empty($request['page']) ? $request['page'] : 0;
    $date_formatter = \Drupal::service('date.formatter');

    foreach ($result as $item) {
      $event_date = new DrupalDateTime($item->event_date);
      $delete = Url::fromUserInput('/admin/config/event?delete_id=' . $item->id_event . '&page=' . $page);
      $edit = Url::fromUserInput('/admin/config/event?id_evento=' . $item->id_event . '&page=' . $page);
      $row = [
        $item->name_event,
        substr($item->description, 0, 150) . '...',
        $item->number_tickets,
        $item->points_tapit,
        $item->event_city,
        $item->name_brand,
        $item->ticket_type == 1 ? t('Double') : t('Personal'),
        $date_formatter->format($event_date->getTimestamp(), 'custom', 'd/m/Y H:i:s'),
        Link::fromTextAndUrl(t('Deactivate product'), $delete),
        Link::fromTextAndUrl(t('Edit'), $edit),
      ];
      $rows[] = $row;
    }

    return [$header, $rows];
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
   * To get all ac.
   */
  public function getConfigBloque() {
    $database = \Drupal::database();
    $query = $database->select('productos', 'ev');
    $query->fields('ev', [
      'id_event',
      'name_event',
      'description',
      'number_tickets',
      'points_tapit',
      'event_city',
      'name_brand',
      'ticket_type',
      'event_date',
      'created',
    ]);
    $respu = $query->execute();

    $rows = [];
    $id = 0;

    foreach ($respu as $try) {
      $delete = Url::fromUserInput('/admin/config/event?delete_id=' . $try->id_event);
      $edit = Url::fromUserInput('/admin/config/event?id_evento=' . $try->id_event);
      $rows[$id] = [
        $try->name_event,
        var_export(substr($try->description, 0, 150), TRUE) . '  ...',
        $try->number_tickets,
        $try->points_tapit,
        $try->event_city,
        $try->name_brand,
        $try->ticket_type == 1 ? 'Boletos Doble' : 'Boletos personales',
        $try->event_date,
        Link::fromTextAndUrl('inactivar producto', $delete),
        Link::fromTextAndUrl('Edit', $edit),
      ];
      $id = $id + 1;
    }

    return $rows;
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

  /**
   * To get all cities from taxonomies.
   */
  public function allConfigBloque() {
    $database = \Drupal::database();
    $query = $database->select('productos', 'ev');
    $query->fields('ev', [
      'id_event',
      'name_event',
      'description',
      'number_tickets',
      'ticket_type',
      'event_date',
      'created',
    ]);
    $respu = $query->execute();

    $respu = $query->execute();

    $term_data = [];
    foreach ($respu as $term) {
      $term_data[$term->id] = '<tr><td>' . $term->id . '</td>' . '<td>' . $term->created . '</td>' . '<td>' . $term->url_btn . '</td>' . '<td>' . $term->texto_btn . '</td>' . '<td>' . $term->color_btn . '</td>' . '<td>' . $term->etiqueta_html . '</td>' . '<td>' . $term->id_sso_config . '</td>' . '<td>' . $term->css_stylos . '</td>' . '<td>' . $term->titulo_bloque . '</td></tr>';
    }

    return $term_data;
  }

  /**
   * To get all cities from taxonomies.
   */
  public function getEvent($id) {
    $database = \Drupal::database();
    $query = $database->select('productos', 'eve');
    // Add extra detail to this query object: a condition, fields and a range.
    $query->condition('id_event', $id, '=');
    $query->fields('eve', [
      'name_event',
      'description',
      'number_tickets',
      'number_tickets_remaining',
      'points_tapit',
      'event_city',
      'name_brand',
      'category',
      'origin_type',
      'url_landing_promo',
      'event_address',
      'ticket_type',
      'event_date',
      'event_status',
      'url_tyc',
      'require_location',
      'require_redirect',
      'size',
      'color',
      'status',
    ]);

    $respu = $query->execute()->fetchAll();

    return $respu;
  }

  /**
   * Function.
   */
  public function editMoreCallbackEvent(array &$form, FormStateInterface $form_state) {
    try {
      $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
      $edi_eve = $request['id_evento'];
      $delete = $request['delete_id'];
      $fid = $form_state->getValue(['image_upload', 0]);
      $fidMobile = $form_state->getValue(['image_upload_mobile', 0]);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $fileDesk = File::load($fid);
      $fileMobie = File::load($fidMobile);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $urlDes = file_create_url($fileDesk->getFileUri());
      $urlMobile = file_create_url($fileMobie->getFileUri());

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
        ])->condition('id_event', $edi_eve, '=')
        ->execute();

      $this->addImagenEvent($urlDes, $urlMobile, $id);
    }
    catch (Exception $e) {
      \Drupal::logger('ABI-INBEV log SSo insert')->info("sso insert config" . print_r($e, 1));
    }
  }

  /**
   * Function.
   */
  public function deletetarmoreCallbackConfig(array &$form, FormStateInterface $form_state) {
    try {
      $delete = \Drupal::request()->request->get('delete_id');
      $id_evento = \Drupal::database()->delete('sso_config')
        ->condition('id_sso_config', $delete, '=')
        ->execute();
      return $this->getConfigBloque();
    }
    catch (Exception $e) {
      \Drupal::logger('ABI-INBEV log SSo insert')->info("sso insert config" . print_r($e, 1));
    }
  }

}
