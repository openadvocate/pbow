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
 *   id = "pbow_report_chart_user_alerts",
 *   admin_label = @Translation("PBOW Report - User Alerts"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartUserAlerts extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get count of all active users for now.
    $active_users = \Drupal::database()->query(
     "SELECT COUNT(uid)
      From users_field_data
      Where uid > 1 AND status = 1 AND access > 0")->fetchField();

    // Users with daily alert.
    $daily = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT n.entity_id)
      FROM user__field_notification n
        JOIN users_field_data u
          ON n.entity_id = u.uid AND u.uid > 1 AND u.status = 1 AND u.access > 0
      Where field_notification_value = 'daily' AND entity_id NOT IN (
        SELECT entity_id
        FROM user__field_notification
        WHERE field_notification_value = 'monthly'
      )")->fetchField();

    // Users with monthly alert.
    $monthly = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT n.entity_id)
      FROM user__field_notification n
        JOIN users_field_data u
          ON n.entity_id = u.uid AND u.uid > 1 AND u.status = 1 AND u.access > 0
      Where field_notification_value = 'monthly' AND entity_id NOT IN (
        SELECT entity_id
        FROM user__field_notification
        WHERE field_notification_value = 'daily'
      )")->fetchField();

    // Users with both alert.
    $both = \Drupal::database()->query(
     "SELECT COUNT(DISTINCT n.entity_id)
      FROM user__field_notification n
        JOIN users_field_data u
          ON n.entity_id = u.uid AND u.uid > 1 AND u.status = 1 AND u.access > 0
      Where field_notification_value = 'daily' AND entity_id IN (
        SELECT entity_id
        FROM user__field_notification
        WHERE field_notification_value = 'monthly'
      )")->fetchField();

    $no_alerts = $active_users - $daily - $monthly - $both;

    // Return table instead for path /report/table
    if (Pbow::isReportTablePath()) {
      return [
        '#type'   => 'table',
        '#rows'   => [
          [['style' => 'font-weight: bold', 'data' => 'No Alerts'],    $no_alerts],
          [['style' => 'font-weight: bold', 'data' => 'Daily Only'],   $daily],
          [['style' => 'font-weight: bold', 'data' => 'Monthly Only'], $monthly],
          [['style' => 'font-weight: bold', 'data' => 'Both'],         $both],
        ],
      ];
    }

    return [
      '#theme' => 'pbow_report_chart_user_alerts',
      '#data' => [$no_alerts, $daily, $monthly, $both]
    ];
  }
}
