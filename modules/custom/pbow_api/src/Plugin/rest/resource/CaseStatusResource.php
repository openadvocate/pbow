<?php

namespace Drupal\pbow_api\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
// use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to return case status.
 *
 * @RestResource(
 *   id = "pbow_case_status",
 *   label = @Translation("Case Status"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/case_status/{case_id}"
 *   }
 * )
 */
class CaseStatusResource extends ResourceBase {
  public function get($case_id = NULL) {
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
        \Drupal::logger('pbow_api')->notice('case_status:error: authorization failed');

        return new ModifiedResourceResponse(['error' => 'Authorization failed.']);
      }
    }
    else {
      \Drupal::logger('pbow_api')->notice('case_status:error: authentication failed');

      return new ModifiedResourceResponse(['error' => 'Authentication failed.']);
    }

    if ($case_id) {
      $nids = \Drupal::entityQuery('node')
        ->condition('type', 'case')
        ->condition('field_case_id', $case_id)
        ->execute();

      if (count($nids)) {
        $case = Node::load(reset($nids));

        if (!empty($case->field_case_status->value)) {
          $value = $case->field_case_status->value;
          $label = $case->field_case_status->getItemDefinition()->getSettings()['allowed_values'][$value];

          \Drupal::logger('pbow_api')->notice("case_status:ok: on $case_id");

          return new ModifiedResourceResponse([
            'case_id' => $case_id,
            'node_id' => $case->id(),
            'status_code' => $value,
            'status_label' => $label
          ]);
        }
      }

      \Drupal::logger('pbow_api')->notice('case_status:error: case not recognized');

      return new ModifiedResourceResponse(['error' => 'Case not recognized.']);
    }

    \Drupal::logger('pbow_api')->notice('case_status:error: invalid input');

    return new ModifiedResourceResponse(['error' => 'Invalid input.']);
  }
}
