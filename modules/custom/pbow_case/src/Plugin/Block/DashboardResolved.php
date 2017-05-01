<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Plugin\Block\DashboardResolved.
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\pbow_case\Pbow;

/**
 * Provides a 'DashboardResolved' block.
 *
 * @Block(
 *  id = "pbow_dashboard_resolved",
 *  admin_label = @Translation("PBOW Dashboard Resolved"),
 *  category = @Translation("Custom Blocks")
 * )
 */
class DashboardResolved extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $rows = [];

    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'case')
      ->condition('field_case_status', Pbow::RESOLVED)
      ->condition('field_case_assigned', $user->id())
      ->execute();

    if (!empty($nids)) {
      $cases = Node::loadMultiple($nids);

      foreach ($cases as $case) {
        $rows[] = [
          $case->field_case_id->value,
          \Drupal::l($case->title->value, Url::fromUri('internal:/node/' . $case->id())),
          ($case->field_case_resolve_hours_att->value + $case->field_case_resolve_hours_para->value). 'hrs'
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
