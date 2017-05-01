<?php 
/*
 * @file
 * Custom Case Conflict Check
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\pbow_case\Form\CaseConflictCheckForm;
use Drupal\pbow_case\Pbow;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides a block the Case You have agreed
 *
 * @Block(
 *   id = "pbow_case_conflict_check",
 *   admin_label = @Translation("PBOW Case Conflict Check"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseConflictCheck extends BlockBase {
  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if (Pbow::isStaffRole()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $case = \Drupal::routeMatch()->getParameter('node');
    $request_form = \Drupal::formBuilder()->getForm(CaseConflictCheckForm::class);

    if (!empty($_SESSION['agreed_to_terms']) and !Pbow::userHasConflictChecked($case)) {
      unset($_SESSION['agreed_to_terms']);

      return [
        '#theme' => 'pbow_case_parties_check',
        '#form' => $request_form,
      ];
    }

    return [];
  }
}
