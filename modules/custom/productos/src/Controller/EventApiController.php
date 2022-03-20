<?php

namespace Drupal\productos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\productos\Form\ReportRegisterForm;
use Drupal\productos\Others\XLSXWriter;

/**
 * Provides a resource to post nodes. Even controler for report.
 */
class EventApiController extends ControllerBase {

  /**
   * Descarga el reporte en excel este metodo esta en construcion.
   */
  public function dowloadReport() {
    $writer = new XLSXWriter();
    list($header, $rows) = $this->getData();
    $result = [$header];
    $date_formatter = \Drupal::service('date.formatter');
    foreach ($rows as $key => $item) {
      $result[] = [
        $item->id,
        $item->name,
        $item->last_name,
        $item->type_document == 1 ? 'cedula' : 'documento extrangeria',
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
        $item->type_document_invited == 1 ? 'cedula' : 'documento extrangeria',
        $item->identification_card_invited,
        $item->email_invited,
        $item->phone_invited,
        $item->name_event,
        $item->points_tapit,
        $item->event_city,
        $item->ticket_type == 1 ? 'Doble' : 'Una persona',
      ];
    }

    $writer->writeSheet($result, 'Sheet1');
    $name = 'public://Report_' . date('Y-m_d_H_i_s') . '.xlsx';
    $path_url = \Drupal::service('file_system')->realpath($name);
    $writer->writeToFile($path_url);
    $url = file_create_url($name);
    return new TrustedRedirectResponse($url);
  }

  /**
   * Create New user asociate for event.
   */
  public function getDateEvent() {
    $database = \Drupal::database();
    $query = $database->select('productos', 'ev');
    $query->condition('event_status', 1, '=');
    $result = $query->fields('ev', ['event_date', 'id_event'])->execute();

    return $result->fetchAll();
  }

  /**
   * Valida si un evento esta para empezar en dos horas, si es asi se desactiva.
   */
  public function validateEventStatus() {
    $array_evet = $this->getDateEvent();
    date_default_timezone_set('America/Bogota');

    $fecha_registro = date("Y-m-d");
    $hora_registro = date("H");

    foreach ($array_evet as &$valor) {
      $date = explode(" ", $valor->event_date);
      $hora_event = explode(":", $date[1]);

      if ($date[0] == $fecha_registro) {
        if (((int) $hora_event[0] - (int) $hora_registro) <= 2) {
          $id_config = \Drupal::database()->update('productos')
            ->fields([
              'event_status' => 0,
              'created' => \Drupal::time()->getRequestTime(),
            ])->condition('id_event', $valor->id_event, '=')
            ->condition('category', 'Tickets', '=')
            ->execute();
        }
      }
    }

    return new JsonResponse([
      'Response' => 'Reclamaste una boleta para este evento',
      'method' => 'POST',
      'status' => 201,
    ]);

  }

  /**
   * Obtiene la data de los eventos para el reporte de usuarios.
   */
  public static function getData($export = FALSE) {
    $header = [
      'id',
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
      'name_event',
      'points_tapit',
      'event_city',
      'ticket_type',
    ];

    $query = \Drupal::database()->select('user_for_event', 'us');
    $query->join('productos', 'e', 'e.id_event = us.id_event');
    $query
      ->fields('us')
      ->fields('e');
    $rows = $query->execute();
    return [$header, $rows];
  }

  /**
   * Listar todos los eventos y filtrar por ciudad/marca/categoría.
   */
  public static function llistEventos() {
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $city = !empty($request['city']) ? $request['city'] : NULL;
    $brand = !empty($request['brand']) ? $request['brand'] : NULL;
    $category = !empty($request['category']) ? $request['category'] : NULL;
    $response = self::productsList($city, $brand, $category);

    return new JsonResponse([
      'event' => $response,
      'method' => 'GET',
      'status' => 200,
    ]);
  }

  /**
   * Get products list.
   *
   * @param string $city
   *   City name.
   * @param string $brand
   *   Brand name.
   * @param string $category
   *   Category name.
   *
   * @return array
   *   Array products list
   */
  public static function productsList($city, $brand, $category) {
    $response = [];
    $query = \Drupal::database()->select('productos', 'e');
    $query->condition('status', 1, '=');
    if (!empty($city)) {
      $query->condition('event_city', '%' . $city . '%', 'LIKE');
    }
    if (!empty($brand)) {
      $query->condition('name_brand', $brand, '=');
    }
    if (!empty($category)) {
      $query->condition('category', $category, '=');
    }
    $query->join('imagen_event', 'er', 'e.id_event = er.id_event');
    $query->fields('er');
    $query->fields('e');
    $result = $query->execute()->fetchAll();

    foreach ($result as $item) {
      $response[] = [
        "id_event" => $item->id_event,
        "name_event" => $item->name_event,
        "points_tapit" => $item->points_tapit,
        "name_brand" => $item->name_brand,
        "description" => $item->description,
        "event_city" => $item->event_city,
        "event_address" => $item->event_address,
        "event_status" => $item->event_status,
        "number_tickets" => $item->number_tickets,
        "number_tickets_remaining" => $item->number_tickets_remaining,
        "category" => $item->category,
        "ticket_type" => $item->ticket_type,
        "event_date" => $item->event_date,
        "url_landing_promo" => $item->url_landing_promo,
        "origin_type" => $item->origin_type,
        "url_tyc" => $item->url_tyc,
        "require_location" => $item->require_location,
        "require_redirect" => $item->require_redirect,
        "created" => $item->created,
        "size" => $item->size,
        "color" => $item->color,
        "status" => $item->status,
        "id" => $item->id,
        "url_image" => file_create_url($item->url_image),
        "url_image_mobile" => file_create_url($item->url_image_mobile),
      ];
    }

    return $response;
  }

  /**
   * Listar todos los departamentos con sus respectivas ciudades en las que se tiene cobertura.
   */
  public static function getCoverageDepartmentsCities($country, $taxonomy_name) {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'tax_d');
    $query->join('taxonomy_term__parent', 'tax_p', 'tax_d.tid = tax_p.entity_id');
    $query->condition('tax_p.bundle', $taxonomy_name, '=');
    if (!empty($country)) {
      $query->condition('tax_d.name', $country, '=');
    }
    $query->fields('tax_d', ['tid']);
    $result = $query->execute()->fetchAssoc();

    if ($result) {
      $tid_p = $result['tid'];
      $query = \Drupal::database()->select('taxonomy_term__parent', 'tax_p');
      $query->join('taxonomy_term_field_data', 'tax_d', 'tax_p.entity_id = tax_d.tid');
      $query->condition('tax_p.parent_target_id', $tid_p, '=');
      $query->fields('tax_p', ['entity_id']);
      $query->fields('tax_d', ['name']);
      $query->orderBy('name', 'asc');
      $result_d_tmp = $query->execute()->fetchAll();

      if ($result_d_tmp) {
        foreach ($result_d_tmp as $item) {
          $query = \Drupal::database()->select('taxonomy_term_field_data', 'tax_d');
          $query->condition('tax_d.tid', $item->entity_id, '=');
          $query->fields('tax_d', ['tid', 'name']);
          $result_d = $query->execute()->fetchAll();
          $response[] = [
            'department' => $result_d[0]->name,
            'cities' => self::getDepartmentCities($result_d[0]->tid),
          ];
        }
      }
    }

    return $response;
  }

  /**
   * Responds to GET request.
   */
  public function endBanner() {
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $brand = $request['brand'];
    $database = \Drupal::database();
    $query = $database->select('brand_data', 'bra');
    $query->condition('brand', $brand, '=');
    $query->fields('bra');
    $result = $query->execute()->fetchAll();
    return new JsonResponse([
      'Brand_banner' => $result,
      'method' => 'GET',
      'status' => 200,
    ]);
  }

  /**
   * Function.
   */
  public function reportRegisterFormExcel() {
    [$header, $rows] = ReportRegisterForm::getReportData(TRUE);
    $filename = 'report_register.xlsx';
    $temp_file = self::writeAndDownloadXlsx($header, $rows);
    $headers = [
      'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Content-Disposition' => 'attachment; filename=' . XLSXWriter::sanitizeSheetname($filename),
    ];
    return new BinaryFileResponse($temp_file, 200, $headers, FALSE);
  }

  /**
   * Function.
   */
  public static function writeAndDownloadXlsx($header, $rows) {
    $header_types = [];
    foreach ($header as $key => $header_item) {
      $header_types[$header_item] = 'string';
    }
    $writer = new XLSXWriter();
    $writer->setAuthor('ABInBev');
    $writer->writeSheet($rows, 'Sheet 1', $header_types);
    // Create a temp file and write.
    $temp_dir = sys_get_temp_dir();
    $temp_file = tempnam($temp_dir, "ab_xlsx_writer_");
    $writer->writeToFile($temp_file);
    return $temp_file;
  }

  /**
   * Function.
   */
  public static function getTaxonomyData($taxonomy_name) {
    $term_data = [];
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy_name);
    foreach ($terms as $term) {
      $term_data[$term->name] = $term->name;
    }
    return $term_data;
  }

  /**
   * Get department cities.
   *
   * @param int $department_id
   *   Department id.
   *
   * @return array
   *   array/data
   */
  public static function getDepartmentCities($department_id) {
    $query = \Drupal::database()->select('taxonomy_term__parent', 'tax_p');
    $query->join('taxonomy_term_field_data', 'tax_d', 'tax_p.entity_id = tax_d.tid');
    $query->condition('tax_p.parent_target_id', $department_id, '=');
    $query->fields('tax_p', ['entity_id']);
    $query->fields('tax_d', ['name']);
    $query->orderBy('tax_d.name', 'asc');
    $result_d_tmp = $query->execute()->fetchAll();

    if ($result_d_tmp) {
      $cities = [];
      foreach ($result_d_tmp as $item) {
        $query = \Drupal::database()->select('taxonomy_term_field_data', 'tax_d');
        $query->condition('tax_d.tid', $item->entity_id, '=');
        $query->fields('tax_d', ['name']);
        $result_d[] = $query->execute()->fetchAll();
      }
    }
    if ($result_d) {
      foreach ($result_d as $item) {
        $cities[] = $item[0]->name;
      }
    }

    return $cities;
  }

  /**
   * Get image id.
   *
   * @param int $event_id
   *   Event ID.
   * @param string $image_request
   *   Image request (d/m), d=desktop, m=mobile.
   *
   * @return int
   *   Return image id
   */
  public static function getImageId($event_id, $image_request) {
    $image_uri = self::getImageData($event_id, $image_request);
    $fileId = 0;
    if (!empty($image_uri)) {
      $image_data_temp = explode('public://', $image_uri);
      if (count($image_data_temp) < 2) {
        $image_data_temp = explode('/', $image_uri);
        $image_name = $image_data_temp[count($image_data_temp) - 1];
        $image_uri = 'public://ab_event/event/all/' . $image_name;
      }
      $file = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $image_uri]);
      $fileId = array_shift($file)->fid->value;
    }

    return $fileId;
  }

  /**
   * Get image url.
   *
   * @param int $event_id
   *   Event ID.
   * @param string $image_request
   *   Image request (d/m), d=desktop, m=mobile.
   *
   * @return string
   *   Return image url
   */
  public static function getImageData($event_id, $image_request) {
    $query = \Drupal::database()->select('imagen_event', 'ie');
    $query->condition('ie.id_event', $event_id, '=');
    if ($image_request == 'd') {
      $query->addField('ie', 'url_image', 'image_uri');
    }
    if ($image_request == 'm') {
      $query->addField('ie', 'url_image_mobile', 'image_uri');
    }
    $result = $query->execute()->fetchObject();

    return $result->image_uri;
  }

  /**
   * Get termn ID by name.
   *
   * @param string $name
   *   Taxonomy name.
   *
   * @return int
   *   Return taxonomy ID
   */
  public static function getTermIdByName($name) {
    $properties = [
      'name' => $name,
    ];
    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    return !empty($term) ? $term->id() : 0;
  }

  /**
   * Create product.
   *
   * @param string $name
   *   Product name.
   * @param int $tapit_points
   *   Tapit point.
   * @param string $brand_name
   *   Brand name.
   * @param string $description
   *   Product description.
   * @param string $event_city
   *   City.
   * @param string $event_address
   *   Address.
   * @param int $number_tickets
   *   Tickets number.
   * @param string $category
   *   Category.
   * @param int $ticket_type
   *   Ticket type.
   * @param string $event_date
   *   Event date.
   * @param string $url_landing_promo
   *   Url landing promo.
   * @param string $origin_type
   *   Origin type.
   * @param string $url_terms_and_conditions
   *   Url terms and conditions.
   * @param string $require_location
   *   Location.
   * @param string $require_redirect
   *   Redirect.
   * @param string $size
   *   Size.
   * @param string $color
   *   Color.
   *
   * @return int|string|null
   *   Return result.
   */
  public static function createProduct($name, $tapit_points, $brand_name, $description, $event_city, $event_address, $number_tickets, $category, $ticket_type, $event_date, $url_landing_promo, $origin_type, $url_terms_and_conditions, $require_location, $require_redirect, $size, $color) {
    try {
      $time = \Drupal::time()->getRequestTime();
      $product_data = [
        'id_event' => NULL,
        'name_event' => $name,
        'points_tapit' => $tapit_points,
        'name_brand' => $brand_name,
        'description' => $description,
        'event_city' => $event_city,
        'event_address' => $event_address,
        'event_status' => 1,
        'number_tickets' => $number_tickets,
        'number_tickets_remaining' => $number_tickets,
        'category' => $category,
        'ticket_type' => !empty($ticket_type) ? $ticket_type : 1,
        'event_date' => !empty($event_date) ? ($event_date . ' UTC') : '',
        'url_landing_promo' => $url_landing_promo,
        'origin_type' => $origin_type,
        'url_tyc' => $url_terms_and_conditions,
        'require_location' => !empty($require_location) ? $require_location : '0',
        'require_redirect' => !empty($require_redirect) ? $require_redirect : '0',
        'created' => $time,
        'size' => $size,
        'color' => $color,
        'status' => 1,
      ];

      return \Drupal::database()->insert('productos')
        ->fields($product_data)
        ->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger(__FUNCTION__)->error($e->getMessage());
    }
    return NULL;
  }

  /**
   * Validate product exist.
   *
   * @param string $name
   *   Product name.
   * @param string $brand_name
   *   Brand name.
   * @param string $event_city
   *   City.
   * @param string $event_date
   *   Event date.
   * @param string $category
   *   Category.
   * @param string $size
   *   Size.
   * @param string $color
   *   Color.
   *
   * @return array
   *   Return result.
   */
  public static function validateProductExist($name, $brand_name, $event_city, $event_date, $category, $size, $color) {
    $query = \Drupal::database()->select('productos', 'p');
    if (!empty($name) && ($category == 'Tickets' || $category == 'Merchandising' || $category == 'Cerveza-Bonos' || $category == 'Otros')) {
      $query->condition('name_event', $name, '=');
    }
    if (!empty($brand_name) && ($category == 'Tickets' || $category == 'Merchandising' || $category == 'Cerveza-Bonos' || $category == 'Otros')) {
      $query->condition('name_brand', $brand_name, '=');
    }
    if (!empty($event_city) && ($category == 'Tickets' || $category == 'Merchandising' || $category == 'Cerveza-Bonos' || $category == 'Otros')) {
      $query->condition('event_city', $event_city, '=');
    }
    if (!empty($event_date) && ($category == 'Tickets' || $category == 'Cerveza-Bonos' || $category == 'Otros')) {
      $query->condition('event_date', ($event_date . ' UTC'), '=');
    }
    if (!empty($category)) {
      $query->condition('category', $category, '=');
    }
    if (!empty($size) && $category == 'Merchandising') {
      $query->condition('size', $size, '=');
    }
    if (!empty($color) && $category == 'Merchandising') {
      $query->condition('color', $color, '=');
    }

    $query->fields('p');
    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * Save product images.
   *
   * @param string $name
   *   Product name.
   * @param string $brand_name
   *   Brand name.
   * @param string $event_city
   *   City.
   * @param string $event_date
   *   Event date.
   * @param string $category
   *   Category.
   * @param string $size
   *   Size.
   * @param string $color
   *   Color.
   * @param string $url_desktop_image
   *   Desktop image.
   * @param string $url_mobile_image
   *   Mobile image.
   *
   * @return int|string|null
   *   Return result.
   */
  public static function saveProductImages($name, $brand_name, $event_city, $event_date, $category, $size, $color, $url_desktop_image, $url_mobile_image) {
    try {
      $product = self::validateProductExist($name, $brand_name, $event_city, $event_date, $category, $size, $color);

      if ($product) {
        $data_images = [
          'id' => NULL,
          'url_image' => $url_desktop_image,
          'url_image_mobile' => $url_mobile_image,
          'id_event' => $product['0']->id_event,
        ];

        return \Drupal::database()->insert('imagen_event')
          ->fields($data_images)
          ->execute();
      }
    }
    catch (\Exception $e) {
      \Drupal::logger(__FUNCTION__)->error($e->getMessage());
    }
    return NULL;

  }

}
