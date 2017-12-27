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
 *   id = "pbow_report_chart_user_engagement",
 *   admin_label = @Translation("PBOW Report - User Engagement"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartUserEngagement extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];

    // Users who activated.
    $data[] = \Drupal::database()->query(
     "SELECT COUNT(uid)
      FROM users_field_data
      WHERE access > 0 AND uid > 1")->fetchField();

    // Users who Terms.
    $data[] = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT uid)
      FROM flagging
      WHERE flag_id = 'conflict_check'")->fetchField();

    // Users who requested
    $data[] = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT uid)
      FROM flagging
      WHERE flag_id = 'case_request'")->fetchField();

    // Users who completed a case or more.
    $data[] = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT field_case_assigned_target_id)
      FROM node__field_case_assigned
      WHERE entity_id IN (
        SELECT entity_id
        FROM node__field_case_status
        WHERE field_case_status_value = :status
      );", [':status' => Pbow::RESOLVED])->fetchField();

    // Return table instead for path /report/table
    if (Pbow::isReportTablePath()) {
      return [
        '#type'   => 'table',
        '#rows'   => [
          [['style' => 'font-weight: bold', 'data' => 'Activated'], $data[0]],
          [['style' => 'font-weight: bold', 'data' => 'Terms'],     $data[1]],
          [['style' => 'font-weight: bold', 'data' => 'Requested'], $data[2]],
          [['style' => 'font-weight: bold', 'data' => 'Completed'], $data[3]],
        ],
      ];
    }

    return [
      '#theme' => 'pbow_report_chart_user_engagement',
      '#data' => $data,
    ];
  }
}
