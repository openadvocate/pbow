<?php

namespace Drupal\pbow_api\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get bundles by entity.
 *
 * @RestResource(
 *   id = "pbow_post_cases",
 *   label = @Translation("Post Cases"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/cases",
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/cases"
 *   }
 * )
 */
class PostCasesResource extends ResourceBase {
  /**
   *  A curent user instance.
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
    */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
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
      $container->get('logger.factory')->get('rest'),
      $container->get('current_user')
    );
  }

  public function post($post = NULL) {
    // Ad hoc authentication.
    // Not using core's http basic auth because it breaks on sites that already
    // have basic authentication like $dev and $test.
    $header = \Drupal::request()->headers;
    $username = $header->get('x-drupal-user');
    $password = $header->get('x-drupal-pass');
    if ($username and $password) {
      $uid = \Drupal::service('user.auth')->authenticate($username, $password);
    }

    if (!empty($uid)) {
      $user = User::load($uid);

      if (!$user->hasRole('api')) {
        \Drupal::logger('pbow_api')->notice('cases:error: authorization failed');

        return new ResourceResponse(['error' => 'Authorization failed.']);
      }
    }
    else {
      \Drupal::logger('pbow_api')->notice('cases:error: authentication failed');

      return new ResourceResponse(['error' => 'Authentication failed.']);
    }

    if ($error = $this->checkPost($post)) {
      \Drupal::logger('pbow_api')->notice("cases:error: $error");

      return new ResourceResponse(['error' => $error]);
    }

    $message = $this->createCases($post, $user->id());

    \Drupal::logger('pbow_api')->notice("cases:ok: $message");

    return new ResourceResponse(['result' => 'OK', 'message' => $message]);
  }

  /**
   * @param post
   *   Call by reference in order to parse and update values (county).
   */
  protected function checkPost(&$post) {
    if (!is_array($post) or empty($post[0])) {
      return "Array of cases expected.";
    }
    if (count($post) > 10) {
      return "Up to 10 cases may be posted at a time.";
    }

    $case_ids = [];
    // Get partner names;
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('partner');
    $partners = array_map(function ($val) {
      return $val->name;
    }, $tree);

    foreach ($post as &$item) {
      // Check required fields.
      if (empty($item['title'])) {
        return "Case title is required.";
      }
      if (empty($item['description'])) {
        return "Case description is required.";
      }
      if (empty($item['case_id'])) {
        return "Case ID is required.";
      }
      if (empty($item['partner'])) {
        return "Partner name is required.";
      }
      if (empty($item['client_name'])) {
        return "Client name is required.";
      }
      if (empty($item['adverse_party_name'])) {
        return "Adverse party name is required.";
      }

      // Check lengths.
      if (strlen($item['title']) > 200) {
        return "Case title length may not exceed 200 chars.";
      }
      if (strlen($item['description']) > 2000) {
        return "Case description length may not exceed 2000 chars.";
      }
      if (strlen($item['case_id']) > 20) {
        return "Case ID length may not exceed 20 chars.";
      }
      if (strlen($item['partner']) > 10) {
        return "Partner name length may not exceed 10 chars.";
      }
      if (strlen($item['client_name']) > 200) {
        return "Client name length may not exceed 200 chars.";
      }
      if (!empty($item['clinet_alias'])) {
        foreach ($item['clinet_alias'] as $cli_name) {
          if (strlen($cli_name) > 200) {
            return "Client alias length may not exceed 200 chars.";
          }
        }
      }
      if (strlen($item['adverse_party_name']) > 200) {
        return "Adverse party name length may not exceed 200 chars.";
      }
      if (!empty($item['adverse_party_alias'])) {
        foreach ($item['adverse_party_alias'] as $adv_name) {
          if (strlen($adv_name) > 200) {
            return "Adverse party alias length may not exceed 200 chars.";
          }
        }
      }

      if (!empty($item['counties'])) {
        $result = $this->parseCountyNamesToTids($item['counties']);

        if (isset($result['error'])) {
          return $result['error'];
        }
        else {
          $item['counties'] = $result;
        }
      }

      if (isset($item['staff_notes']) and strlen($item['staff_notes']) > 2000) {
        return "Staff notes length may not exceed 2000 chars.";
      }

      // Check partner name.
      if (!in_array($item['partner'], $partners)) {
        return "'$item[partner]' partner name not recognized.";
      }

      // Check deadline.
      if (!empty($item['deadline'])) {
        if (!preg_match('#\d{4}-\d{2}-\d{2}#', $item['deadline'])) {
          return "Deadline must be in the form 'YYYY-MM-DD.";
        }
        if (strtotime($item['deadline']) < strtotime('today')) {
          return "Deadline must be set in the future.";
        }
      }

      // Check duplicate case ID.
      // Against own submission
      if (in_array($item['case_id'], $case_ids)) {
        return "Case ID ($item[case_id]) is not unique in your submission.";
      }
      else {
        $case_ids[] = $item['case_id'];
      }

      // Against database.
      if ($this->hasDuplicateCaseId($item['case_id'])) {
        return "Case ID ($item[case_id]) already exists in the system.";
      }
    }
  }

  protected function createCases($post, $uid) {
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('partner');
    $partner_tids = array_reduce($tree, function ($result, $item) {
      $result[$item->name] = $item->tid;
      return $result;
    }, array());

    foreach ($post as $item) {
      $case = Node::create([
        'type'               => 'case',
        'uid'                => $uid,
        'title'              => $item['title'],
        'body'               => _filter_autop($item['description']),
        'field_case_source'  => 'api',
        'field_case_id'      => $item['case_id'],
        'field_case_partner' => $partner_tids[$item['partner']],
        'field_case_client_name'   => $item['client_name'],
        'field_case_client_alias'  => !empty($item['client_alias']) ? $item['client_alias'] : NULL,
        'field_case_adverse_name'  => $item['adverse_party_name'],
        'field_case_adverse_alias' => !empty($item['adverse_party_alias']) ? $item['adverse_party_alias'] : NULL,
        'field_case_deadline'    => !empty($item['deadline']) ? $item['deadline'] : NULL,
        'field_county'           => $item['counties'],
        'field_case_staff_notes' => _filter_autop($item['staff_notes']),
      ]);

      $case->save();
    }

    return "Created " . count($post) . ' Cases.';
  }

  protected function hasDuplicateCaseId($case_id) {
    static $case_ids;

    if (empty($case_ids)) {
      $query = \Drupal::database()->select('node__field_case_id', 'n');
      $query->addField('n', 'field_case_id_value');
      $case_ids = $query->execute()->fetchCol();
    }

    return in_array($case_id, $case_ids);
  }

  protected function parseCountyNamesToTids($array) {
    static $counties;

    if (empty($counties)) {
      $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('county');
      $counties = array_reduce($tree, function ($result, $item) {
        $result[$item->name] = $item->tid;
        return $result;
      }, array());
    }

    $tids = [];
    foreach ($array as $name) {
      if (isset($counties[$name])) {
        $tids[] = $counties[$name];
      }
      else {
        return ['error' => 'Unknown county name: ' . $name];
      }
    }

    return $tids;
  }
}
