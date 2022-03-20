<?php

namespace Drupal\productos\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "validate_user_event",
 *   label = @Translation("Validar usuarios event"),
 *   uri_paths = {
 *     "canonical" = "v1/api/validateuserevent"
 *   }
 * )
 */
class ValidateUserEventosGetResource extends ResourceBase {
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
      $container->get('logger.factory')->get('validate_user_event'),
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
    $document_number = $request['document_number'];
    $id_event = $request['id_event'];

    $database = \Drupal::database();
    $query = $database->select('user_for_event', 'us');
    $query->condition('id_event', $id_event, '=');
    $query->condition('identification_card', $document_number, '=');
    $result = $query->fields('us', ['id_event'])->execute();
    if ($result->fetchAll()) {
      return new JsonResponse([
        'Response' => 'El Usuario ya esta registrado a este evento ',
        'estatus_validate' => TRUE,
        'method' => 'POST',
        'status' => 201,
      ]);
    }
    else {
      return new JsonResponse([
        'Response' => 'El Usuario no esta registrado a este evento',
        'estatus_validate' => FALSE,
        'method' => 'POST',
        'status' => 201,
      ]);
    }

  }

}
