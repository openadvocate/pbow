<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Plugin\Block\DashboardMatching.
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\pbow_case\Pbow;
use Drupal\user\Entity\User;

/**
 * Provides a 'DashboardMatching' block.
 *
 * @Block(
 *  id = "pbow_dashboard_matching",
 *  admin_label = @Translation("PBOW Dashboard Matching"),
 *  category = @Translation("Custom Blocks")
 * )
 */
class DashboardMatching extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());
    $rows = [];

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');
    if ($flaggings = $flag_service->getFlagFlaggings($flag, $user)) {
      $requested_nids = array_map(function($val) {
        return $val->entity_id->value;
      }, $flaggings);
    }

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'case')
      ->condition('field_case_status', [Pbow::AVAILABLE, Pbow::REQUESTED], 'IN');

    if (!empty($requested_nids)) {
      $query->condition('nid', $requested_nids, 'NOT IN');
    }

    // Match case type, population, county
    $population = array_map(function ($val) {
      return $val['target_id'];
    }, $user->field_population->getValue());
    $case_type = array_map(function ($val) {
      return $val['target_id'];
    }, $user->field_case_type->getValue());
    $county = array_map(function ($val) {
      return $val['target_id'];
    }, $user->field_county->getValue());

    $group = $query->orConditionGroup()
      ->condition('field_population', $population ?: [''], 'IN')
      ->condition('field_case_type',  $case_type  ?: [''], 'IN')
      ->condition('field_county',     $county     ?: [''], 'IN');
    $query->condition($group);

    $nids = $query->execute();

    if (!empty($nids)) {
      $cases = Node::loadMultiple($nids);

      foreach ($cases as $case) {
        $rows[] = [
          $case->field_case_id->value,
          \Drupal::l($case->title->value, Url::fromUri('internal:/node/' . $case->id())),
          (!empty($case->field_case_deadline->value)) ? "Due: " . date('n/d/Y', strtotime($case->field_case_deadline->value)) : ''
        ];
      }
      $header = ['Case', 'Title', 'Deadline'];
    }
    else {
      $rows[] = ['None'];
      $header = ['Empty'];
    }

    return [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $rows,
      '#cache'  => ['max-age' => 0]
    ];
  }

}
