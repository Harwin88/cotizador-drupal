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
 *   id = "Delete_user_event",
 *   label = @Translation("Delete usuer eventos"),
 *   uri_paths = {
 *     "canonical" = "v1/api/delete/user/event"
 *   }
 * )
 */
class DeleteUserEventGetResource extends ResourceBase {
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
      $container->get('logger.factory')->get('Delete_user_event'),
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
    $documento = $request['documento'];

    $query = \Drupal::database()->delete('user_for_event');
    $query->condition('identification_card', $documento, '=')->execute();

    return new JsonResponse([
      'Response' => 'el usuario fue eliminar',
      'method' => 'POST',
      'status' => 200,
    ]);
  }

}
