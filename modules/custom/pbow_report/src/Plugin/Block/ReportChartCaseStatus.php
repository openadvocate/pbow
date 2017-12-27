<?php 
/*
 * @file
 * Custom Report block.
 */

namespace Drupal\pbow_report\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block for Report.
 *
 * @Block(
 *   id = "pbow_report_chart_case_status",
 *   admin_label = @Translation("PBOW Report - Case Status"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartCaseStatus extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [
      $this->caseStatusCount(Pbow::INCOMING),
      $this->caseStatusCount(Pbow::AVAILABLE),
      $this->caseStatusCount(Pbow::REQUESTED),
      $this->caseStatusCount(Pbow::ASSIGNED),
      $this->caseStatusCount(Pbow::RESOLVED),
    ];

    // Return table instead for path /report/table
    if (Pbow::isReportTablePath()) {
      return [
        '#type'   => 'table',
        '#rows'   => [
          [['style' => 'font-weight: bold', 'data' => 'Incoming'],  $data[0]],
          [['style' => 'font-weight: bold', 'data' => 'Available'], $data[1]],
          [['style' => 'font-weight: bold', 'data' => 'Requested'], $data[2]],
          [['style' => 'font-weight: bold', 'data' => 'Assigned'],  $data[3]],
          [['style' => 'font-weight: bold', 'data' => 'Resolved'],  $data[4]],
        ],
      ];
    }

    return [
      '#theme' => 'pbow_report_chart_case_status',
      '#data' => $data,
    ];
  }

  protected function caseStatusCount($status) {
    return \Drupal::entityQuery('node')
      ->condition('type', 'case')
      ->condition('field_case_status', $status)
      ->count()
      ->execute();
  }
}
