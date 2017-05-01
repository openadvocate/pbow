<?php 
/*
 * @file
 * Custom Report block.
 */

namespace Drupal\pbow_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block for Report.
 *
 * @Block(
 *   id = "pbow_report_chart_case_activity",
 *   admin_label = @Translation("PBOW Report - Chart Case Activity"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartCaseActivity extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $creates = $assigns = [];
    for ($i = 0; $i > -52; $i--) {
      $from = strtotime("last sunday $i week");
      $to = strtotime("next sunday $i week") - 1;

      // Create count.
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->addExpression('COUNT(nid)');
      $query->where("created BETWEEN $from AND $to");
      $count = $query->execute()->fetchField();

      $creates[] = $count;

      // Assign count.
      $query = \Drupal::database()->select('node__field_case_time_assigned', 'n');
      $query->addExpression('COUNT(entity_id)');
      $query->where("unix_timestamp(str_to_date(field_case_time_assigned_value, '%Y-%m-%dT%H')) BETWEEN $from AND $to");
      $count = $query->execute()->fetchField();

      $assigns[] = $count;
    }

    $creates = array_reverse($creates);
    $assigns = array_reverse($assigns);

    $data = [
      'labels' => join(', ', array_map(function($val) {
        return $val % 4 ? '' : "\"$val\"";
      }, range(1, 52))),
      'creates' => join(', ', $creates),
      'assigns' => join(', ', $assigns),
    ];

    return [
      '#theme' => 'pbow_report_chart_case_activity',
      '#data' => $data,
    ];
  }
}
