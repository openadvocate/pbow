<?php 
/*
 * @file
 * Custom Report block.
 */

namespace Drupal\pbow_report\Plugin\Block;

use Drupal\Core\Link;
use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block for Report.
 *
 * @Block(
 *   id = "pbow_report_chart_cases_by_population",
 *   admin_label = @Translation("PBOW Report - Cases By Population"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartCasesByPopulation extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $result = \Drupal::database()->query(
     "SELECT t.tid, t.name, COUNT(DISTINCT s.entity_id) AS cases, COUNT(DISTINCT u.uid) AS users
      FROM taxonomy_term_field_data t
        -- Cases
        LEFT JOIN node__field_population p ON t.tid = p.field_population_target_id
        LEFT JOIN node__field_case_status s ON p.entity_id = s.entity_id AND s.field_case_status_value BETWEEN 20 AND 50

        -- Users
        LEFT JOIN user__field_population up ON t.tid = up.field_population_target_id
        LEFT JOIN users_field_data u ON up.entity_id = u.uid AND u.status = 1
      WHERE t.vid = 'population'
      GROUP BY t.tid, t.name
      ORDER BY t.name");

    // Return table instead for path /report/table
    if (Pbow::isReportTablePath()) {
      $rows = [];
      foreach ($result as $row) {
        $rows[] = [
          Link::createFromRoute($row->name, 'entity.taxonomy_term.canonical', ['taxonomy_term' => $row->tid]),
          $row->cases,
          $row->users
        ];
      }

      return [
        '#type'   => 'table',
        '#header' => ['Population', 'Cases', 'Users'],
        '#rows'   => $rows,
      ];
    }

    $data = [];

    foreach ($result as $row) {
      $data['label'][] = $row->name;
      $data['cases'][] = $row->cases;
      $data['users'][] = $row->users;
    }

    $data['height'] = 50 + count($data['label']) * 15;

    return [
      '#theme' => 'pbow_report_chart_cases_by_population',
      '#data' => $data,
    ];
  }
}
