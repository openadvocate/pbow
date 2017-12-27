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
 *   id = "pbow_report_chart_activated_accounts",
 *   admin_label = @Translation("PBOW Report - Activated Accounts"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ReportChartActivatedAccounts extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $count_loggedin = \Drupal::entityQuery('user')
      ->condition('uid', 1, '>')
      ->condition('access', 0, '>')
      ->count()
      ->execute();

    $count_never_loggedin = \Drupal::entityQuery('user')
      ->condition('uid', 1, '>')
      ->condition('access', 0)
      ->count()
      ->execute();

    $count_ilas = \Drupal::entityQuery('node')
      ->condition('field_case_partner', 1)
      ->count()
      ->execute();

    $count_ivlp = \Drupal::entityQuery('node')
      ->condition('field_case_partner', 2)
      ->count()
      ->execute();

    $data = [
      'loggedin' => $count_loggedin,
      'never_loggedin' => $count_never_loggedin,
      'ilas' => $count_ilas,
      'ivlp' => $count_ivlp,
    ];

    // Return table instead for path /report/table
    if (Pbow::isReportTablePath()) {
      return [
        'Title (Activated accounts)' => [
          '#markup' => '<h2 class="block-title">Activated Accounts</h2>'
        ],
        'Activated accounts' => [
          '#type'   => 'table',
          '#rows'   => [
            [['style' => 'font-weight: bold', 'data' => 'Logged in at least once'],
              $count_loggedin],
            [['style' => 'font-weight: bold', 'data' => 'Never logged in'],
              $count_never_loggedin],
          ],
        ],
        'Title (Cases by partner)' => [
          '#markup' => '<h2 class="block-title">Cases by Partner</h2>'
        ],
        'Cases by partner' => [
          '#type'   => 'table',
          '#rows'   => [
            [['style' => 'font-weight: bold', 'data' => 'ILAS'],
              $count_ilas],
            [['style' => 'font-weight: bold', 'data' => 'IVLP'],
              $count_ivlp],
          ],
        ]
      ];
    }

    return [
      '#theme' => 'pbow_report_chart_activated_accounts',
      '#data' => $data,
    ];
  }
}
