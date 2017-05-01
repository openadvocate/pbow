<?php 
/*
 * @file
 * Custom Case Request
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\pbow_case\Form\CaseRequestForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a block to request a case
 *
 * @Block(
 *   id = "pbow_case_request",
 *   admin_label = @Translation("PBOW Case Request"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseRequest extends BlockBase {
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
    $request_form = \Drupal::formBuilder()->getForm(CaseRequestForm::class);

    if (Pbow::userHasConflictChecked($case) and !Pbow::userHasRequested($case)) {
      return [
        '#theme' => 'pbow_case_request',
        '#form' => $request_form,
      ];
    }

    return [];
  }
}
