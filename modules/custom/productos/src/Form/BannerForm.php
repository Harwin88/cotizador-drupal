<?php

namespace Drupal\productos\Form;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Configure example settings for this site.
 */
class BannerForm extends ConfigFormBase {
  /*
   * @var string Config settings
   */
  const SETTINGS = 'app.core';

  /**
   * Get form Id string.
   */
  public function getFormId() {
    return 'ab_event_banner';
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
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $delete = $request['delete_id'];
    // $delete = $request['delete_id'];
    $respuesta = [];
    $user_rol = [];
    $id = 0;
    $id = isset($edi) ? $edi : -1;
    // $id = 0;
    $user_rol = \Drupal::currentUser()->getRoles();
  

   
    if (isset($delete)) {
      $respuesta = $this->deleteDataBrand($delete);
    }

    $form['contactform'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['banner_brand'] = [
      '#type' => 'details',
      '#title' => 'administrar banner de los eventos.',
      '#group' => 'contactform',
    ];

    $form['banner_brand']['name_brand'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => self::getIdTaxonomy('marcas', $respuesta[0]->brand),
      '#title' => 'Seleccione la marca',
      '#empty_option' => t('Selecciona una'),
      '#options' => self::getBrand(),
      '#attributes' => [
        'placeholder' => t('Selecciona una'),
        'class' => ['abi-select_control'],
      ],
    ];

    $validators = [
      'file_validate_extensions' => ['jpg jpeg png'],
    // 10 MB limit
      'file_validate_size' => [10 * 1024 * 1024],
    ];

    $form['banner_brand']['image_banner'] = [
      '#type' => 'managed_file',
      '#name' => 'Sube una imagen para el Banner',
      '#title' => t('Sube una imagen para el Banner'),
      '#size' => 15,
      '#required' => TRUE,
      '#description' => "Peso máximo 8 MB. jpg, png",
      '#upload_validators' => $validators,
      '#upload_location' => 'public://ab_event/event/all/',
      '#suffix' => '<label for="files[image_upload]">Peso máximo 8 MB. jpg, png</label>',
      '#attributes' => [
        'class' => ['inputfile'],
        'accept' => ['image/x-png,image/jpeg'],
        'class' => ['abi-file_control'],
      ],

    ];

    $form['banner_brand']['url_image_brand'] = [
      '#type' => 'managed_file',
      '#name' => 'Logo de la marca',
      '#title' => t('Logo de la marca'),
      '#size' => 15,
      '#required' => TRUE,
      '#description' => "Peso máximo 8 MB. jpg, png",
      '#upload_validators' => $validators,
      '#upload_location' => 'public://ab_event/event/all/',
      '#suffix' => '<label for="files[image_upload]">Peso máximo 8 MB. jpg, png</label>',
      '#attributes' => [
        'class' => ['inputfile'],
        'accept' => ['image/x-png,image/jpeg'],
        'class' => ['abi-file_control'],
      ],

    ];

    $form['banner_brand']['email'] = [
      '#title' => 'email de la marca',
      '#type' => 'email',
      '#default_value' => isset($respuesta[0]->email) ? $respuesta[0]->email : '',
      '#disabled' => FALSE,
    ];

    $form['banner_brand']['phlone_brand'] = [
      '#title' => 'celular de la marca',
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->phlone_brand) ? $respuesta[0]->phlone_brand : '',
      '#disabled' => FALSE,
    ];

    $form['banner_brand']['nit_brand'] = [
      '#title' => 'nit de la marca',
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->nit_brand) ? $respuesta[0]->nit_brand : '',
      '#disabled' => FALSE,
    ];


    $form['banner_brand']['text_one'] = [
      '#title' => 'Texto principal del banner',
      '#type' => 'textarea',
      '#default_value' => isset($respuesta[0]->copy1) ? $respuesta[0]->copy1 : '',
      '#disabled' => FALSE,
    ];

    $form['banner_brand']['text_two'] = [
      '#title' => 'Texto secundario del banner',
      '#type' => 'textarea',
      '#default_value' => isset($respuesta[0]->copy2) ? $respuesta[0]->copy2 : '',
      '#disabled' => FALSE,
    ];

    if (isset($edi)) {
      $form['banner_brand']['boton-agregar'][] = [
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
            '#value' => $this->t('Editar'),
            '#type' => 'submit',
          ],
        ],
      ];
      $form['banner_brand']['boton-agregar'][] = [
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
            '#value' => $this->t('Agregar'),
            '#type' => 'submit',
          ],
        ],
      ];

    }
    else {

      if (!isset($delete)) {
        $form['banner_brand']['boton-agregar'][] = [
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
              '#value' => $this->t('Agregar'),
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
      $form['banner_brand']['boton-agregar'][] = [
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
            '#value' => $this->t('Eliminar'),
            '#ajax' => [
              'callback' => '::deletetarmoreCallbackConfig',
              'event' => 'click',
              'wrapper' => 'preset-wrapper-config',
            ],
          ],
        ],
      ];

      $form['banner_brand']['boton-add-config'][] = [
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
            '#value' => $this->t('Agregar'),
            // '#ajax' => array(
            // 'callback' =>  '::addmoreCallback_config',
            // 'event' => 'click',
            // 'wrapper' => 'preset-wrapper-config',
            // ),
          ],
        ],
      ];

    }
    // Option table.
    $form['banner_brand']['options'] = [
      '#type' => 'table',
      '#tree' => TRUE,
      '#header' => [
        $this->t('Imagen'),
        $this->t('Banner texto uno'),
        $this->t('Banner texto dos '),
        $this->t('Marca Banner'),
      ],
      '#rows' => $this->getConfigBloque(),
      '#prefix' => '<div id="preset-wrapper-config" >',
      '#suffix' => '</div>',
      '#attributes' => [
        // Option 3: Class appears on the anchor tag.
        'style' => 'display: block; overflow-x: auto; overflow-y: auto;',
      ],
    ];

    return $form;
  }

  /**
   * Function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $edi_banner = $request['id_banner'];
    $delete = $request['delete_id'];

    if (!isset($edi_banner)) {
      if ($form_state->getValue('name_brand') == '') {
        $form_state->setErrorByName('name_brand', $this->t('Ingrese un valor.'));
      }
      if ($form_state->getValue('text_one') == '') {
        $form_state->setErrorByName('text_one', $this->t('Ingrese un valor.'));
      }
      if ($form_state->getValue('text_two') == '') {
        $form_state->setErrorByName('text_two', $this->t('Ingrese un valor.'));
      }

      return $form_state;
    }
  }

  /**
   * Public function addmoreCallback_config(array &$form, FormStateInterface $form_state)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $urlDes = '';
      $urlMobile = '';
      $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
      $edi_banner = $request['id_banner'];
      $delete = $request['delete_id'];
      $brand = Term::load($form_state->getValue('name_brand'))->get('name')->value;
      $fid = $form_state->getValue(['image_banner', 0]);
      $fid_brand = $form_state->getValue(['url_image_brand', 0]);
      $fileDesk = File::load($fid);
      $fileDesk->setPermanent();
      $fileDesk->save();
      $brand_url = File::load($fid_brand);
      $fileDesk->setPermanent();
      $fileDesk->save();
      $urlDes = file_create_url($fileDesk->getFileUri());
      $urlDes_brand = file_create_url($brand_url->getFileUri());
      if (!$edi_banner) {
        $id = \Drupal::database()->insert('brand_data')
          ->fields([
            'url_banner' => $urlDes,
            'copy1' => $form_state->getValue('text_one'),
            'copy2' => $form_state->getValue('text_two'),
            'email' => $form_state->getValue('email'),
            'phlone_brand' => $form_state->getValue('phlone_brand'),
            'nit_brand' => $form_state->getValue('nit_brand'),
            'brand' => $brand,
            'url_image_brand' => $urlDes_brand,
            'created' => \Drupal::time()->getRequestTime(),
            'update' => \Drupal::time()->getRequestTime(),
          ])
          ->execute();
        return $form;
      }
      if ($urlDes != '' && $edi_banner != '') {
        $id = \Drupal::database()->update('brand_data')
          ->fields([
            'copy1' => $form_state->getValue('text_one'),
            'copy2' => $form_state->getValue('text_two'), 
            'email' => $form_state->getValue('email'),
            'phlone_brand' => $form_state->getValue('phlone_brand'),
            'nit_brand' => $form_state->getValue('nit_brand'),
            'brand' => $brand,
            'url_banner' => $urlDes,
            'created' => \Drupal::time()->getRequestTime(),
          ])->condition('id', $edi_banner, '=')->execute();
        return $form;
      }
      else {
        if ($urlDes_brand != '' && $edi_banner != '') {
          $id = \Drupal::database()->update('brand_data')
            ->fields([
              'copy1' => $form_state->getValue('text_one'),
              'copy2' => $form_state->getValue('text_two'),
              'email' => $form_state->getValue('email'),
              'phlone_brand' => $form_state->getValue('phlone_brand'),
              'nit_brand' => $form_state->getValue('nit_brand'),
              'brand' => $brand,
              'url_image_brand' => $urlDes_brand,
              'created' => \Drupal::time()->getRequestTime(),
            ])->condition('id', $edi_banner, '=')->execute();
          return $form;
        }
        else {
          $id = \Drupal::database()->update('brand_data')
            ->fields([
              'copy1' => $form_state->getValue('text_one'),
              'copy2' => $form_state->getValue('text_two'),
              'email' => $form_state->getValue('email'),
              'phlone_brand' => $form_state->getValue('phlone_brand'),
              'nit_brand' => $form_state->getValue('nit_brand'),
              'brand' => $brand,
              'created' => \Drupal::time()->getRequestTime(),
            ])->condition('id', $edi_banner, '=')->execute();
          return $form;
        }
      }
    }
    catch (Exception $e) {
      \Drupal::logger('ABI-INBEV event insert')->info("event insert config" . print_r($e, 1));
    }
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
   * To get all ac.
   */
  public function getConfigBloque() {

    $database = \Drupal::database();
    $query = $database->select('brand_data', 'bann');
    $query->fields('bann', [
      'id',
      'url_banner',
      'copy1',
      'copy2',
      'email',
      'phlone_brand',
      'nit_brand',
      'brand',
    ]);
    $respu = $query->execute();

    $rows = [];
    $id = 0;

    foreach ($respu as $try) {
      $delete    = Url::fromUserInput('/admin/banner/events?delete_id=' . $try->id);
      $edit      = Url::fromUserInput('/admin/banner/events?id_banner=' . $try->id);
      $rows[$id] = [
        $try->url_banner,
        $try->copy1,
        $try->copy2,
        $try->email,
        $try->phlone_brand,
        $try->nit_brand,
        $try->brand,        Link::fromTextAndUrl('inactivar banner', $delete),
        Link::fromTextAndUrl('Edit', $edit),
      ];
      $id        = $id + 1;
    }

    return $rows;
  }

  /**
   * To get all cities from taxonomies.
   */
  public function getBanner($id) {
    $database = \Drupal::database();
    $query = $database->select('brand_data', 'banner');
    // Add extra detail to this query object: a condition, fields and a range.
    $query->condition('id', $id, '=');
    $query->fields('banner', [
      'copy1',
      'copy2',
      'email',
      'phlone_brand',
      'nit_brand',
      'brand',
    ]);

    $respu = $query->execute()->fetchAll();

    return $respu;
  }

  /**
   * To get all cities from taxonomies.
   */
  public function deleteDataBrand($id) {
    $database = \Drupal::database();
    $query = $database->delete('brand_data');
    // Add extra detail to this query object: a condition, fields and a range.
    $query->condition('id', $id, '=');
   
    $respu = $query->execute();

    return $respu;
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
   * Function.
   */
  public function editMoreCallbackEvent(array &$form, FormStateInterface $form_state) {
    try {
      $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
      $edi_banner = $request['id_banner'];
      $delete = $request['delete_id'];
      $fid = $form_state->getValue(['image_upload', 0]);
      $fidMobile = $form_state->getValue(['image_upload_mobile', 0]);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $fileDesk = File::load($fid);
      $fileMobie = File::load($fidMobile);
      // $ash = ash('sa256', $form_state->getValue('dni').$fid . $salt);
      $urlDes = file_create_url($fileDesk->getFileUri());
      $urlMobile = file_create_url($fileMobie->getFileUri());

      $id = \Drupal::database()->update('brand_data')
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
        ])->condition('id_event', $edi_banner, '=')
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
      $id_banner = \Drupal::database()->delete('sso_config')
        ->condition('id_sso_config', $delete, '=')
        ->execute();
      return $this->getConfigBloque();
    }
    catch (Exception $e) {
      \Drupal::logger('ABI-INBEV log SSo insert')->info("sso insert config" . print_r($e, 1));
    }

  }

}
