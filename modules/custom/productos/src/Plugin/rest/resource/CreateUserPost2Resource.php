<?php

namespace Drupal\productos\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a resource to post nodes.
 *
 * @RestResource(
 *   id = "rest_crear_usuarios",
 *   label = @Translation("crear usuarios para eventos"),
 *   uri_paths = {
 *     "create" = "/v1/api/createuser"
 *   }
 * )
 */
class CreateUserPost2Resource extends ResourceBase {

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

    $name = $data['name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $department = $data['department'];
    $city = $data['city'];
    $address = $data['address'];
    $additional_address_information = $data['additional_address_information'];
    $postal_code = $data['postal_code'];
    $type_document = $data['type_document'];
    $ident_card = $data['ident_card'];
    $id_event = $data['id_event'];
    $point_tapit = $data['point_tapit'];
    $id = 0;
    $name_invited = $data['name_invited'];
    $last_name_invited = $data['last_name_invited'];
    $email_invited = $data['email_invited'];
    $phone_invited = $data['phone_invited'];
    $type_document_invited = $data['type_document_invited'];
    $identification_card_invited = $data['identification_card_invited'];

    if ($id_event >= 1) {
      $point_event = $this->pointTapitEvents($id_event);
      $identification = $this->validateUserEvents($id_event, $ident_card);
    }
    else {

      return new JsonResponse([
        'Response' => 'Este evento no es valido',
        'method' => 'POST',
        'status' => 100,
      ]);

    }

    if ($point_tapit >= $point_event) {
      if ($identification[0]->id_event == NULL) {
        $id = \Drupal::database()->insert('user_for_event')
          ->fields([
            'name' => $name,
            'last_name' => $last_name,
            'type_document' => $type_document,
            'identification_card' => $ident_card,
            'email' => $email,
            'phone' => $phone,
            'department' => $department,
            'city' => $city,
            'address' => $address,
            'additional_address_information' => $additional_address_information,
            'postal_code' => $postal_code,
            'name_invited' => $name_invited,
            'last_name_invited' => $last_name_invited,
            'type_document_invited' => $type_document_invited,
            'identification_card_invited' => $identification_card_invited,
            'email_invited' => $email_invited,
            'phone_invited' => $phone_invited,
            'id_event' => $id_event,
            'created' => \Drupal::time()->getRequestTime(),
          ])
          ->execute();

        $this->changeTicke($id_event);
        return new JsonResponse([
          'Response' => 'el usuario fue creado con exito',
          'method' => 'POST',
          'status' => 200,
        ]);

      }
      else {
        return new JsonResponse([
          'Response' => 'Ya esta registrado con este Numero de documento',
          'method' => 'POST',
          'status' => 100,
        ]);
      }
    }
    else {
      return new JsonResponse([
        'Response' => ' tus puntos no son suficientes',
        'method' => 'POST',
        'status' => 100,
      ]);
    }
  }

  /**
   * Validar los puntos para los eventos.
   */
  public function pointTapitEvents($id_event) {
    $database = \Drupal::database();
    $query = $database->select('productos', 'ev');
    $query->condition('id_event', $id_event, '=');
    $result = $query->fields('ev', ['points_tapit'])->execute();

    return $result->fetchAll()[0]->points_tapit;
  }

  /**
   * Valida si el usuario esta registrado al evento.
   */
  public function validateUserEvents($id_event, $ident_card) {
    $database = \Drupal::database();
    $query = $database->select('user_for_event', 'us');
    $query->condition('id_event', $id_event, '=');
    $query->condition('identification_card', $ident_card, '=');
    $result = $query->fields('us', ['id_event'])->execute();

    return $result->fetchAll();
  }

  /**
   * Descuenta los tickets del evento / bloteas.
   */
  public function changeTicke($id_event) {

    // Metodo que trae la cantida disponible de tickes por id del evento.
    $numero_ticke = $this->selectNumeroTicke($id_event);

    $id_config = \Drupal::database()->update('productos')
      ->fields([
        'number_tickets_remaining' => $numero_ticke - 1,
        'created' => \Drupal::time()->getRequestTime(),
      ])->condition('id_event', $id_event, '=')
      ->execute();

    return new JsonResponse([
      'Response' => 'Reclamaste una boleta para este evento ',
      'method' => 'POST',
      'status' => 201,
    ]);
  }

  /**
   * Cantidad de boletas actuales.
   */
  public function selectNumeroTicke($id_event) {
    $database = \Drupal::database();
    $query = $database->select('productos', 'ev');
    $query->condition('id_event', $id_event, '=');
    $result = $query->fields('ev', ['number_tickets_remaining'])->execute();
    return $result->fetchAll()[0]->number_tickets_remaining;
  }

}
