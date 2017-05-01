<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Plugin\Block\DashboardRequested.
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'DashboardRequested' block.
 *
 * @Block(
 *  id = "pbow_dashboard_requested",
 *  admin_label = @Translation("PBOW Dashboard Requested"),
 *  category = @Translation("Custom Blocks")
 * )
 */
class DashboardRequested extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');
    $rows = [];

    if ($flaggings = $flag_service->getFlagFlaggings($flag, $user)) {
      $nids = array_map(function($val) {
        return $val->entity_id->value;
      }, $flaggings);

      $cases = Node::loadMultiple($nids);

      foreach ($cases as $case) {
        if ($case->field_case_assigned->target_id == $user->id()) {
          continue;
        }

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
