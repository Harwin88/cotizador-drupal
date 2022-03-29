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
    $edi = !empty($request['id_product']) ? $request['id_product'] : NULL;
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
      $respuesta = $this->getReportDataId($id);
    }

    if(isset($delete)) {
      $this->deleteDateProduct($delete);
    }
    
    $form['info'] = [
      '#type' => 'details',
      '#title' => t('PRODUCT CREATE'),
      '#open' => FALSE,
    ];

    $form['info']['name_product'] = [
      '#title' => t('Product name'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->name_product) ? $respuesta[0]->name_product : '',
      '#disabled' => FALSE,
    ];

    $form['info']['stock'] = [
      '#title' => t('cantida del producto en el inventario.'),
      '#default_value' => isset($respuesta[0]->stock) ? $respuesta[0]->stock : '',
      '#type' => 'textfield',
    ];

    if (isset($edi)) {
      $form['info']['stock_restante'] = [
        '#title' => t('stock restante.'),
        '#type' => 'number',
        '#default_value' => isset($respuesta[0]->stock_restante) ? $respuesta[0]->stock_restante : '',
        '#disabled' => FALSE,
      ];
    }

    $form['info']['precio_unitario'] = [
      '#title' => t('precio Por Unidad.'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->precio_unitario) ? $respuesta[0]->precio_unitario : '',
      '#disabled' => FALSE,
    ];

    $form['info']['minima_compra'] = [
      '#title' => t('Minima compra.'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->minima_compra) ? $respuesta[0]->minima_compra : '',
      '#disabled' => FALSE,
    ];

    $form['info']['horas_entrega'] = [
      '#title' => t('Numero de horas para entregar.'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->horas_entrega) ? $respuesta[0]->horas_entrega : '',
      '#disabled' => FALSE,
    ];

    $form['info']['descuento_reila'] = [
      '#title' => t('descuento por compras iguales o mayores a 2 veses la minima compra  %.'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->descuento_reila) ? $respuesta[0]->descuento_reila : '',
      '#disabled' => FALSE,
    ];

    $form['info']['utilidad_neta'] = [
      '#title' => t('porsentaje de utilida del producto se le suma.'),
      '#type' => 'number',
      '#default_value' => isset($respuesta[0]->utilidad_neta) ? $respuesta[0]->utilidad_neta : '',
      '#disabled' => FALSE,
    ];

    $form['info']['guia_uso_producto_url'] = [
      '#title' => t('Guia Informacion tecnica del provedor no es obligatorio'),
      '#type' => 'textfield',
      '#default_value' => isset($respuesta[0]->guia_uso_producto_url) ? $respuesta[0]->guia_uso_producto_url : '',
      '#disabled' => FALSE,
    ];

    $form['info']['estado_producto'] = [
      '#type' => 'radios',
      '#title' => t('Oculta o muestra este producto si el proveedor no cumple con los terminos del contrato.'),
      '#default_value' => isset($respuesta[0]->estado_producto) ? $respuesta[0]->estado_producto : 1,
      '#options' => [
        '1' => t('Activo'),
        '2' => t('Bloqueado'),
      ],
    ];


    $form['info']['name_brand'] = [
      '#title' => t('Brand'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('marcas', !empty($respuesta[0]->name_brand) ? $respuesta[0]->name_brand : ''),
      '#empty_option' => t('Select'),
      '#options' => self::getTaxonomyList("marcas"),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['size'] = [
      '#title' => t('Unidades de medida'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('unidades', !empty($respuesta[0]->size) ? $respuesta[0]->size : ''),
      '#empty_option' => t('Select'),
      '#options' => self::getTaxonomyList('unidades'),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['color'] = [
         '#type' => 'color',
         '#title' => t('Color Del Producto'),
         '#default_value' => $respuesta[0]->color ? $respuesta[0]->color : '',
       ];

    $form['info']['tipo_producto'] = [
      '#title' => t('Tipo de producto'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('producttipo', !empty($respuesta[0]->tipo_producto) ? $respuesta[0]->tipo_producto: ''),
      '#empty_option' => t('Select'),
      '#options' => self::getTaxonomyList('producttipo'),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['category'] = [
      '#title' => t('Categoria del producto un solo nivel.'),
      '#type' => 'select',
      '#default_value' => self::getIdTaxonomy('categorias', !empty($respuesta[0]->category) ? $respuesta[0]->category : ''),
      '#empty_option' => t('Select'),
      '#options' => self::getTaxonomyList('categorias'),
      '#attributes' => [
        'placeholder' => t('Select'),
        'class' => ['abi-select_control'],
      ],
    ];

    $form['info']['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => isset($respuesta[0]->description) ? $respuesta[0]->description : '',
      '#disabled' => FALSE,
    ];

    $validators = [
      'file_validate_extensions' => ['jpg jpeg png'],
      // 10 MB limit.
      'file_validate_size' => [10 * 1024 * 1024],
    ];

    $form['info']['url_image'] = [
      '#type' => 'managed_file',
      '#title' => t('Desktop image'),
      '#size' => 20,
      '#description' => t("Limit size") . ' 10 MB. ' . t('Allowed extensions') . ': jpg, png',
      '#upload_validators' => $validators,
      '#upload_location' => 'public://Catalogo/all/',
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

    $form['info']['url_image_mobile'] = [
      '#type' => 'managed_file',
      '#title' => t('Mobile Image'),
      '#size' => 15,
      '#description' => t("Limit size") . ' 8 MB. ' . t('Allowed extensions') . ': jpg, png',
      '#upload_validators' => $validators,
      '#upload_location' => 'public://ab_product/product/all/',
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
              // 'product' => 'click',
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
              'product' => 'click',
              'wrapper' => 'preset-wrapper-config',
            ],
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
      $edi_eve = !empty($request['id_product']) ? $request['id_product'] : NULL;
      $delete = !empty($request['delete_id']) ? $request['delete_id'] : NULL;

     if (!isset($edi_eve)) {
        /* if ($form_state->getValue('category') == '') {
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
        if ($form_state->getValue('name_product') == '') {
          $form_state->setErrorByName('name_product', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('precio_unitario') == '') {
          $form_state->setErrorByName('precio_unitario', $this->t('Ingrese un valor.'));
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
        if ($form_state->getValue('name_brand') == '') {
          $form_state->setErrorByName('name_brand', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('category') == '') {
          $form_state->setErrorByName('category', $this->t('Ingrese un valor.'));
        }
        if ($form_state->getValue('origin_type') == '') {
          $form_state->setErrorByName('origin_type', $this->t('Ingrese un valor.'));
        }*/

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
        $edi_eve = !empty($request['id_product']) ? $request['id_product'] : NULL;
        $delete = !empty($request['delete_id']) ? $request['delete_id'] : NULL;
        $brand = Term::load($form_state->getValue('name_brand'))->get('name')->value;
        $category = Term::load($form_state->getValue('category'))->get('name')->value;
        $size = Term::load($form_state->getValue('size'))->get('name')->value;

        $fid = $form_state->getValue(['url_image', 0]);
        $fidMobile = $form_state->getValue(['url_image_mobile', 0]);
        $fileDesk = File::load($fid);
        $fileMobie = File::load($fidMobile);
        $urlDes = $fileDesk->getFileUri();
        $urlMobile = $fileMobie->getFileUri();

        if ($edi_eve) {
          $id = \Drupal::database()->update('productos')
            ->fields([
              'name_product' => $form_state->getValue('name_product'),
              'description' => $form_state->getValue('description'),
              'precio_unitario' => $form_state->getValue('precio_unitario'),
              'precio_unitario' => $form_state->getValue('precio_unitario'),
              'category' => $category,
              'stock' => $form_state->getValue('stock'),
              'stock_restante' => $form_state->getValue('stock_restante'),
              'minima_compra' => $form_state->getValue('minima_compra'),
              'name_brand' => $brand,
              'tipo_producto' => $form_state->getValue('tipo_producto') == 1 ? 1 : 2,
              'horas_entrega' => $form_state->getValue('horas_entrega'),
              'descuento_reila' => $form_state->getValue('descuento_reila'),
              'utilidad_neta' => $form_state->getValue('utilidad_neta'),
              'guia_uso_producto_url' => $form_state->getValue('guia_uso_producto_url'),
              'estado_producto' => $form_state->getValue('estado_producto') == 1 ? 1 : 2,
              'size' => $size,
              'color' => $form_state->getValue('color'),
              'created' => \Drupal::time()->getRequestTime(),
            ])->condition('id_product', $edi_eve, '=')
            ->execute();

          $this->editproductImage($urlDes, $urlMobile, $edi_eve);

          return $form;
        }

        $fileDesk->setPermanent();
        $fileDesk->save();
        $fileMobie->setPermanent();
        $fileMobie->save();

        $id = \Drupal::database()->insert('productos')
        ->fields([
          'name_product' => $form_state->getValue('name_product'),
          'description' => $form_state->getValue('description'),
          'precio_unitario' => $form_state->getValue('precio_unitario'),
          'precio_unitario' => $form_state->getValue('precio_unitario'),
          'category' => $category,
          'stock' => $form_state->getValue('stock'),
          'stock_restante' => $form_state->getValue('stock'),
          'minima_compra' => $form_state->getValue('minima_compra'),
          'name_brand' => $brand,
          'tipo_producto' => $form_state->getValue('tipo_producto') == 1 ? 1 : 2,
          'horas_entrega' => $form_state->getValue('horas_entrega'),
          'descuento_reila' => $form_state->getValue('descuento_reila'),
          'utilidad_neta' => $form_state->getValue('utilidad_neta'),
          'guia_uso_producto_url' => $form_state->getValue('guia_uso_producto_url'),
          'estado_producto' => $form_state->getValue('estado_producto') == 1 ? 1 : 2,
          'size' => $size,
          'color' => $form_state->getValue('color'),
          'created' => \Drupal::time()->getRequestTime(),
        ])
          ->execute();

        $this->addImagenproduct($urlDes, $urlMobile, $id);

        return $form;
      }
      catch (Exception $e) {
        \Drupal::logger('ABI-INBEV product insert')->info("product insert config" . print_r($e, 1));
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
      t('id'),
      t('Product name'),
      t('Description'),
      t('precio unitario'),
      t('stock'),
      t('stock restante'),
      t('minima compra'),
      t('Sponsor brand'),
      t('tipo producto'),
      t('horas entrega'),
      t('descuento por 2 veses la minima compra%'),
      t('created'),
      t('Edit'),
      t('delete'),
    ];

    $query = \Drupal::database()->select('productos', 'pt');
    $query->fields('pt', [
      'id_product',
      'name_product',
      'description',
      'precio_unitario',
      'stock',
      'stock_restante',
      'minima_compra',
      'name_brand',
      'tipo_producto',
      'horas_entrega',
      'descuento_reila',
      'created',
      'size'
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
        ->condition('name_product', '%' . $search_filter . '%', 'LIKE')
        ->condition('name_brand', '%' . $search_filter . '%', 'LIKE');
      $query->condition($or);
    }

    $result = $query
      ->orderBy('name_product', 'ASC')
      ->execute();

    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $page = !empty($request['page']) ? $request['page'] : 0;
    $date_formatter = \Drupal::service('date.formatter');

    foreach ($result as $item) {
      $created = new DrupalDateTime($item->created);
      $delete = Url::fromUserInput('/admin/config/product?delete_id=' . $item->id_product . '&page=' . $page);
      $edit = Url::fromUserInput('/admin/config/product?id_product=' . $item->id_product . '&page=' . $page);
      $row = [
     $item->id_product,
     $item->name_product,
     $item->description,
     '$'.$item->precio_unitario,
     $item->stock." - ".$item->size,
     $item->stock_restante." - ".$item->size,
     $item->minima_compra." - ".$item->size,
     $item->name_brand,
     $item->tipo_producto,
     $item->horas_entrega,
     $item->descuento_reila.'%',
     $item->created,
     $date_formatter->format($created->getTimestamp(), 'custom', 'd/m/Y H:i:s'),
     Link::fromTextAndUrl(t('Edit'), $edit),
     Link::fromTextAndUrl(t('delete'), $delete),
      ];
      $rows[] = $row;
    }

    return [$header, $rows];
  }

   /**
   * Function.
   */
  public static function getReportDataId($id) {
    $query = \Drupal::database()->select('productos', 'p');
    $query->condition('id_product', $id, '=');
    $query->fields('p');
    $result = $query->execute()->fetchAll();

    return $result;
  }

    /**
   * To get all cities from taxonomies.
   */
  public function deleteDateProduct($id) {
    $database = \Drupal::database();
    $query = $database->delete('productos');
    // Add extra detail to this query object: a condition, fields and a range.
    $query->condition('id_product', $id, '=');
   
    $respu = $query->execute();

    return $respu;
  }


  /**
   * To get all cities from taxonomies.
   */
  private function getTaxonomyList($name) {
    $vid = $name;
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
   * Add imagen for product.
   */
  public function editproductImage($urlDes, $urlMobile, $id) {
    \Drupal::database()->update('imagen_peoductos')
      ->fields([
        'url_image' => $urlDes,
        'url_image_mobile' => $urlMobile,
      ])->condition('id_product', $id, '=')
      ->execute();
  }

  /**
   * Add imagen for product.
   */
  public function addImagenproduct($urlDes, $urlMobile, $id) {
    \Drupal::database()->insert('imagen_peoductos')
      ->fields([
        'url_image' => $urlDes,
        'url_image_mobile' => $urlMobile,
        'id_product' => $id,
      ])
      ->execute();
  }


}
