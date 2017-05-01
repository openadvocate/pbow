<?php 
/*
 * @file
 * Custom Case View Parties
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\pbow_case\Form\CaseViewPartiesForm;
use Drupal\pbow_case\Pbow;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides a block the Case You have agreed
 *
 * @Block(
 *   id = "pbow_view_parties",
 *   admin_label = @Translation("PBOW Case View Parties"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseViewParties extends BlockBase {
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
    $request_form = \Drupal::formBuilder()->getForm(CaseViewPartiesForm::class);

    if (empty($_SESSION['agreed_to_terms']) and
        !Pbow::userHasConflictChecked($case) and
        !Pbow::userHasRequested($case))
    {
      return [
        '#theme' => 'pbow_case_parties_check',
        '#form' => $request_form,
      ];
    }

    return [];
  }
}
