<?php

namespace Drupal\productos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\productos\Controller\EventApiController;

/**
 * Class ProductsImportForm.
 *
 * @package Drupal\productos\Form
 */
class ProductsImportForm extends FormBase {

  /**
   * Get form Id string.
   */
  public function getFormId() {
    return 'productos_products_import_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user_rol = \Drupal::currentUser()->getRoles();

    if ($user_rol[1] == "operaciones") {
      $form['#attached']['library'][] = 'productos/productos';
    }

    $form['columns'] = [
      '#markup' => t('CSV row format (need header). Fields: ') . '<b>name</b>, <b>tapit_points</b>, <b>brand_name</b>, description, <b>event_city</b>, event_address, <b>number_tickets</b>, *<b>category</b>, <b>ticket_type</b>, *event_date, url_landing_promo, origin_type, url_terms_and_conditions, <b>require_location</b>, <b>require_redirect</b>, size, color, <b>url_desktop_image</b>, <b>url_mobile_image</b><br>
        <br>The fields in <b>bold</b> are required.
        <br>*If in the <b>category</b> field you send <b>Tickets</b> you must send the <b>event_date</b> field<br>
        <br>- <b>brand_name</b> field: Existing brands can be found <a href="/admin/structure/taxonomy/manage/brands/overview" target="_blank">here</a>
        <br>- <b>category</b> field: Existing categories can be found <a href="/admin/structure/taxonomy/manage/category/overview" target="_blank">here</a>
        <br>- The format of the field <b>event_date</b> is "YYYY-MM-DD HH:MM:SS", example: 2022-02-26 18:09:09
        <br>- <b>require_location</b> field, <b>0</b>: No, <b>1</b>: Yes
        <br>- <b>require_redirect</b> field, <b>0</b>: No, <b>1</b>: Yes',
    ];

    $form['delimiter'] = [
      '#type' => 'select',
      '#title' => t('Delimiter'),
      '#required' => TRUE,
      '#options' => [
        ',' => ',',
        ';' => ';',
      ],
      '#default_value' => ';',
    ];

    $form['file_data'] = [
      '#type' => 'managed_file',
      '#title' => t('CSV file'),
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => [
          'csv CSV',
        ],
      ],
      '#upload_location' => 'public://products_uploads/' . date('Y_m_d') . '/',
    ];

    $form['submit_data'] = [
      '#type' => 'submit',
      '#value' => t('Start import'),
    ];

    return $form;
  }

  /**
   * Function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Function.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($file = File::load($form_state->getValue('file_data')[0])) {
      $file->setPermanent();
      $file->save();
      $inputFileName = \Drupal::service('file_system')->realpath($file->getFileUri());
      $outputFileName = \Drupal::service('file_system')->realpath('public://products_uploads/');
      $command = 'split -l 500 "' . $inputFileName . '" ' . $outputFileName . '/Split_';
      exec($command);
      $files = glob($outputFileName . '/Split_*');

      $operations = [];
      $delimiter = $form_state->getValue('delimiter');
      $header = [];
      if (!empty($files)) {
        list(, $header) = ProductsImportForm::csvtoarray($files[0], $delimiter, $header);
      }

      $total_files = count($files);
      foreach ($files as $idx => $file) {
        $message = t('File ') . ($idx + 1) . '/' . $total_files;
        $operations[] = [
          '\Drupal\productos\Form\ProductsImportForm::processFiles',
          [
            $file,
            $message,
            $delimiter,
            $header,
          ],
        ];
      }
      $batch = [
        'title' => t('Creating/Updating record'),
        'operations' => $operations,
        'init_message' => t('Starting'),
        'finished' => '\Drupal\productos\Form\ProductsImportForm::processItemFileDataFinish',
      ];
      batch_set($batch);
    }

  }

  /**
   * Function.
   */
  public static function processFiles($file, $message, $delimiter, $header, &$context) {
    $context['message'] = t('Loading ') . $file;
    list($rows,) = ProductsImportForm::csvtoarray($file, $delimiter, $header);

    $operations = [];
    foreach ($rows as $row) {
      $operations[] = [
        '\Drupal\productos\Form\ProductsImportForm::processItemFileData',
        [$row, $message],
      ];
    }
    if (!isset($context['results']['total'])) {
      $context['results']['total'] = 0;
    }
    $context['results']['total'] += count($rows);
    unlink($file);
    batch_set([
      'operations' => $operations,
    ]);
  }

  /**
   * Process each record to create products.
   *
   * @param string $item
   *   Item.
   * @param string $message
   *   String.
   * @param string $context
   *   Context.
   */
  public static function processItemFileData($item, $message, &$context) {
    try {
      $context['message'] = t('Processing ') . $message . t('. Creating/Updating record: ') . $item['name'];

      $name = trim($item['name']);
      $tapit_points = trim($item['tapit_points']);
      $brand_name = trim($item['brand_name']);
      $description = trim($item['description']);
      $event_city = trim($item['event_city']);
      $event_address = trim($item['event_address']);
      $number_tickets = intval($item['number_tickets']);
      $category = trim($item['category']);
      $ticket_type = intval($item['ticket_type']);
      $event_date = trim($item['event_date']);
      $url_landing_promo = trim($item['url_landing_promo']);
      $origin_type = trim($item['origin_type']);
      $url_terms_and_conditions = trim($item['url_terms_and_conditions']);
      $require_location = trim($item['require_location']);
      $require_redirect = trim($item['require_redirect']);
      $size = trim($item['size']);
      $color = trim($item['color']);
      $url_desktop_image = trim($item['url_desktop_image']);
      $url_mobile_image = trim($item['url_mobile_image']);

      if ($require_location != 'require_location' && $require_redirect != 'require_redirect') {
        $response = EventApiController::validateProductExist($name, $brand_name, $event_city, $event_date, $category, $size, $color);
        if (empty($response)) {
          $create_product = EventApiController::createProduct($name, $tapit_points, $brand_name, $description, $event_city, $event_address, $number_tickets, $category, $ticket_type, $event_date, $url_landing_promo, $origin_type, $url_terms_and_conditions, $require_location, $require_redirect, $size, $color);
          if ($create_product) {
            EventApiController::saveProductImages($name, $brand_name, $event_city, $event_date, $category, $size, $color, $url_desktop_image, $url_mobile_image);
          }
        }
      }
    }
    catch (\Exception $ex) {
      \Drupal::logger(__FUNCTION__)->info('error <pre>@error</pre>',
        ['@error' => $ex->getMessage()]);
    }
  }

  /**
   * Batch 'finished' callback.
   */
  public static function processItemFileDataFinish($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        ($results['total'] - 1),
        t('A processed record'), '@count ' . t('processed records')
      );
      \Drupal::messenger()->addMessage($message);
    }
    else {
      $message = t('The process finished with errors');
      \Drupal::messenger()->addMessage($message);
    }
  }

  /**
   * Convert csv file to array.
   *
   * @param string $filename
   *   File name.
   * @param string $delimiter
   *   Delimite.
   * @param string $header
   *   Header.
   *
   * @return array
   *   Return array with data.
   */
  public static function csvtoarray($filename, $delimiter, $header) {
    /* Load the object of the file by it's fid */
    if (!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }
    $data = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
      while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        if (empty($header)) {
          $header = preg_replace('/[[:^print:]]/', '', $row);
        }
        else {
          $data[] = array_combine($header, $row);
        }
      }
      fclose($handle);
    }

    return [self::convertToUtf8Recursively($data), $header];
  }

  /**
   * Encode array to utf8 recursively.
   *
   * @param string $dat
   *   Dat.
   *
   * @return array|string
   *   Return result.
   */
  public static function convertToUtf8Recursively($dat) {
    if (is_string($dat)) {
      return utf8_encode($dat);
    }
    elseif (is_array($dat)) {
      $ret = [];
      foreach ($dat as $i => $d) {
        $ret[$i] = self::convertToUtf8Recursively($d);
      }

      return $ret;
    }
    elseif (is_object($dat)) {
      foreach ($dat as $i => $d) {
        $dat->$i = self::convertToUtf8Recursively($d);
      }

      return $dat;
    }
    else {
      return $dat;
    }
  }

}
