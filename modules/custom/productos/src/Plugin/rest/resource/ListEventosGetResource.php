<?php

namespace Drupal\productos\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\productos\Controller\EventApiController;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "list_event",
 *   label = @Translation("List event"),
 *   uri_paths = {
 *     "canonical" = "v1/api/list/event"
 *   }
 * )
 */
class ListEventosGetResource extends ResourceBase {
  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
  public function __construct(
    array $config,
    $module_id,
    $module_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($config, $module_id, $module_definition, $serializer_formats, $logger);

    $this->loggedUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('list_event'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *    Throws exception expected.
   */
  public function get() {
    $request = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $city = !empty($request['city']) ? $request['city'] : NULL;
    $brand = !empty($request['brand']) ? $request['brand'] : NULL;
    $category = !empty($request['category']) ? $request['category'] : NULL;
    $response = EventApiController::productsList($city, $brand, $category);

    return new JsonResponse([
      'event' => $response,
      'method' => 'GET',
      'status' => 200,
    ]);
  }

}
