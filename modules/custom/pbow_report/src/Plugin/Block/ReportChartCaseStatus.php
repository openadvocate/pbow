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
