<?php 
/*
 * @file
 * Custom Case Title
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a block the Case Title
 *
 * @Block(
 *   id = "pbow_case_title",
 *   admin_label = @Translation("PBOW Case Title"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseTitle extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $case = \Drupal::routeMatch()->getParameter('node');

    return [
      '#markup' => "<h1 class='page-header'>{$case->field_case_id->value} : {$case->title->value}</h1>"
    ];
  }

}
