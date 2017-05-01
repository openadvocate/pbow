<?php 
/*
 * @file
 * Custom Case import
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a block the Case import
 *
 * @Block(
 *   id = "pbow_case_import",
 *   admin_label = @Translation("PBOW Case Import"),
 *   category = @Translation("Custom Blocks")
 * )
 */

class CaseImport extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $block = [
      '#markup' => " ",
      '#theme' => 'pbow_case_import',
      '#cache' => [
        'max-age' => 0
      ],
    ];

    return $block;

  }


}
