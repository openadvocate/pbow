<?php 
/*
 * @file
 * Custom Case Staff Notes
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;


/**
 * Provides a block the Case Staff Notes
 *
 * @Block(
 *   id = "pbow_case_staff_notes",
 *   admin_label = @Translation("PBOW Case Staff Notes"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseStaffNotes extends BlockBase {
  protected $case;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->case = \Drupal::routeMatch()->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if (Pbow::isStaffRole()) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->case->field_case_staff_notes->value) {
      return [
        '#theme' => 'pbow_case_staff_notes',
        '#case' => $this->case,
      ];
    }

    return [];
  }
}
