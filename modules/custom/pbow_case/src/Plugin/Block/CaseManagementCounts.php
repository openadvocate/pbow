<?php 
/*
 * @file
 * Custom Case Management Counts
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block the Case Management Counts
 *
 * @Block(
 *   id = "pbow_case_management_counts",
 *   admin_label = @Translation("PBOW Case Management Counts"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseManagementCounts extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $counts = [
      Pbow::INCOMING  => 0,
      Pbow::AVAILABLE => 0,
      Pbow::REQUESTED => 0,
      Pbow::ASSIGNED  => 0,
      Pbow::RESOLVED  => 0,
      Pbow::ARCHIVED  => 0,
    ];

    foreach ($this->statusCounts() as $row) {
      $counts[$row->status] = $row->count;
    }

    $block = [
      '#theme' => 'pbow_case_management_counts',
      '#counts' => $counts,
      '#cache' => [
        'max-age' => 0
      ],
    ];

    $block['#attached']['library'][] = 'pbow_case/pbow_case';

    return $block;
    
  }

  /**
   * As node view count (via Statistics module) does not increase in panels,
   * increase it here. Code copied from statistics.php
   */
  protected function statusCounts() {
    $query = \Drupal::database()->select('node__field_case_status', 's');
    $query->addField('s', 'field_case_status_value', 'status');
    $query->addExpression('COUNT(*)', 'count');

    return $query->groupBy('field_case_status_value')
      ->orderBy('field_case_status_value')
      ->execute();
  }
}
