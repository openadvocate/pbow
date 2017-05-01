<?php 
/*
 * @file
 * Custom Case Body
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a block the Case Body
 *
 * @Block(
 *   id = "pbow_case_body",
 *   admin_label = @Translation("PBOW Case Body"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseBody extends BlockBase {

  protected $case;

  public function __construct() {
    $this->case = \Drupal::routeMatch()->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->viewCountUp();

    return [
      '#theme' => 'pbow_case_body',
      '#case' => $this->case,
    ];
  }

  /**
   * As node view count (via Statistics module) does not increase in panels,
   * increase it here. Code copied from statistics.php
   */
  protected function viewCountUp() {
    \Drupal::database()->merge('node_counter')
      ->key('nid', $this->case->id())
      ->fields(array(
        'daycount' => 1,
        'totalcount' => 1,
        'timestamp' => REQUEST_TIME,
      ))
      ->expression('daycount', 'daycount + 1')
      ->expression('totalcount', 'totalcount + 1')
      ->execute();
  }
}
