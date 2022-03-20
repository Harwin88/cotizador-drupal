<?php

namespace Drupal\productos\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a resource to post nodes.
 *
 * @RestResource(
 *   id = "discounts_points",
 *   label = @Translation("Descontar puntos tap it"),
 *   uri_paths = {
 *     "create" = "/v1/api/discountspoints"
 *   }
 * )
 */
class DescountPointPostResource extends ResourceBase {

  use StringTranslationTrait;

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_examples'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Creates a new node.
   *
   * @param mixed $data
   *   Data to create the node.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    $config = \Drupal::config('productos.settings');

    $uid = $data['userId'];
    $estate = $data['originType'];
    $quantity = $data['quantity'];

    $data_json = [
      "userId" => $uid,
      "originType" => $estate,
      "quantity" => intval($quantity),
    ];

    $curl = curl_init();

    if ($quantity < 0) {
      curl_setopt_array($curl, [
        CURLOPT_URL => $config->get('estatus') == "1" ? $config->get('dominio_dev') : $config->get('dominio_prod'),
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data_json),
        CURLOPT_HTTPHEADER => [
          $config->get('estatus') == "1" ? $config->get('key_dev') : $config->get('key_prod'),
          'Content-Type: application/json',
        ],
      ]);

      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      curl_close($curl);
    }
    return new ResourceResponse($response, $httpcode);

  }

}
