<?php

namespace Drupal\pbow_case\Plugin\Action;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an action that approves Case.
 *
 * @Action(
 *   id = "case_approve_action",
 *   label = @Translation("Approve Case"),
 *   type = "node"
 * )
 */
class ApproveCase extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $entity->set('field_case_status', Pbow::AVAILABLE);
    $entity->set('field_case_time_available', Pbow::now());
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    return $object->access('update', $account, $return_as_object);
  }

}
