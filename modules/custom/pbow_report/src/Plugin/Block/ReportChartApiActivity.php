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
 *   id = "pbow_report_chart_api_activity",
 *   admin_label = @Translation("PBOW Report - Chart API Activity"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartApiActivity extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $cases = $status = [];
    for ($i = 0; $i > -52; $i--) {
      $from = strtotime("last sunday $i week");
      $to = strtotime("next sunday $i week") - 1;

      // Cases count.
      $query = \Drupal::database()->select('watchdog', 'w');
      $query->addExpression('COUNT(wid)');
      $query->where("type = 'pbow_api' AND message LIKE 'cases:%'");
      $query->where("timestamp BETWEEN $from AND $to");
      $count = $query->execute()->fetchField();

      $cases[] = $count;

      // Status count.
      $query = \Drupal::database()->select('watchdog', 'w');
      $query->addExpression('COUNT(wid)');
      $query->where("type = 'pbow_api' AND message LIKE 'case_status:%'");
      $query->where("timestamp BETWEEN $from AND $to");
      $count = $query->execute()->fetchField();

      $status[] = $count;
    }

    $cases = array_reverse($cases);
    $status = array_reverse($status);

    $data = [
      'labels' => join(', ', array_map(function($val) {
        return $val % 4 ? '' : "\"$val\"";
      }, range(1, 52))),
      'cases' => join(', ', $cases),
      'status' => join(', ', $status),
    ];

    return [
      '#theme' => 'pbow_report_chart_api_activity',
      '#data' => $data,
    ];
  }
}
