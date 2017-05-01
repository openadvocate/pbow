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
 *   id = "pbow_report_chart_user_activity",
 *   admin_label = @Translation("PBOW Report - Chart User Activity"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartUserActivity extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $accesses = [];
    for ($i = 0; $i > -52; $i--) {
      $from = strtotime("last sunday $i week");
      $to = strtotime("next sunday $i week") - 1;

      // Access count.
      $count = \Drupal::entityQuery('user')
        ->condition('uid', 1, '>')
        ->condition('access', [$from, $to], 'BETWEEN')
        ->count()
        ->execute();

      $accesses[] = $count;
    }

    // Weekly traffic from Google Analytics
    $traffic = [];
    if (function_exists('google_analytics_reports_api_report_data')) {
      $params = array(
        'metrics' => array('ga:pageviews'),
        'dimensions' => array('ga:week'),
        'segment' => 'gaid::-1',
        'sort_metric' => array('ga:week'),
        'start_date' => strtotime('-51 weeks'),
        'end_date' => strtotime('now'),
      );
      $feed = google_analytics_reports_api_report_data($params);
      if (!empty($feed->results->rows)) {
        foreach ($feed->results->rows as $row) {
          $traffic[] = $row['pageviews'];
        }
      }
    }
    else {
      drupal_set_message('Enable "Google Analytics Reports API" module to show web traffic graph.');
    }

    $accesses = array_reverse($accesses);

    $data = [
      'labels' => join(', ', array_map(function($val) {
        return $val % 4 ? '' : "\"$val\"";
      }, range(1, 52))),
      'activity' => join(', ', $accesses),
      'traffic' => join(', ', $traffic),
    ];

    return [
      '#theme' => 'pbow_report_chart_user_activity',
      '#data' => $data,
    ];
  }
}
