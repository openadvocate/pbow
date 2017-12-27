<?php 
/*
 * @file
 * Custom Case Parties
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;


/**
 * Provides a block the Case parties
 *
 * @Block(
 *   id = "pbow_case_parties",
 *   admin_label = @Translation("PBOW Case Parties"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseParties extends BlockBase {
  protected $case;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->case = \Drupal::routeMatch()->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if (!Pbow::isStaffRole() and
        !Pbow::userHasRequested($this->case) and
        !Pbow::userHasConflictChecked($this->case) and
        empty($_SESSION['agreed_to_terms']))
    {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->case->show_case_detail = !empty($this->case->field_case_details->value) 
      && (Pbow::isStaffRole() || Pbow::userHasConflictChecked($this->case));

    return [
      '#theme' => 'pbow_case_parties',
      '#case' => $this->case,
    ];
  }
}
