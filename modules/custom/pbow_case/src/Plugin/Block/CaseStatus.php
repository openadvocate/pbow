<?php 
/*
 * @file
 * Custom Case Status
 */

namespace Drupal\pbow_case\Plugin\Block;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Block\BlockBase;
use Drupal\pbow_case\Form\CaseDeleteForm;
use Drupal\pbow_case\Form\CaseRevokeForm;
use Drupal\pbow_case\Form\CaseArchiveForm;
use Drupal\pbow_case\Form\CaseResolveForm;
use Drupal\pbow_case\Form\CaseAssignOverlapForm;
use Drupal\pbow_case\Form\CaseMakeAvailableForm;

/**
 * Provides a block the Case Status
 *
 * @Block(
 *   id = "pbow_case_status",
 *   admin_label = @Translation("PBOW Case Status"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class CaseStatus extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $case = \Drupal::routeMatch()->getParameter('node');

    if (Pbow::isStaffRole()) {
      $template = 'pbow_case_status_staff';

      $builder = \Drupal::formBuilder();
      $case->form = [
        'avail'   => $builder->getForm(CaseMakeAvailableForm::class),
        'delete'  => $builder->getForm(CaseDeleteForm::class),
        'archive' => $builder->getForm(CaseArchiveForm::class),
        'assign'  => $case->field_case_status->value == Pbow::REQUESTED
                   ? $builder->getForm(CaseAssignOverlapForm::class)
                   : NULL,
        'revoke'  => $builder->getForm(CaseRevokeForm::class),
      ];

      $case->stat = statistics_get($case->id());
      $case->requests = $this->requesterInfo($case);
      $case->assigned_picture = $case->field_case_assigned->isEmpty() ? NULL : Pbow::userPictureUrl($case->field_case_assigned->entity);

      // In order to print list value instead of list key.
      // $outcome_field = $case->field_case_resolve_outcome;
      // if ($outcome_field->value) {
      //   $case->field_case_resolve_outcome->value_print = $outcome_field->getSetting('allowed_values')[$outcome_field->value];
      // }

      // $close_type_field = $case->field_case_resolve_close_type;
      // if ($close_type_field->value) {
      //   $case->field_case_resolve_close_type->value_print = $close_type_field->getSetting('allowed_values')[$close_type_field->value];
      // }
    }
    else {
      $template = 'pbow_case_status_user';

      $case->check   = Pbow::userHasConflictChecked($case);
      $case->request = Pbow::userHasRequested($case);
      $case->assign  = Pbow::userIsAssigned($case);
      $case->reject  = Pbow::userIsRejected($case);

      // In order to print list value instead of list key.
      // $outcome_field = $case->field_case_resolve_outcome;
      // if ($outcome_field->value) {
      //   $case->field_case_resolve_outcome->value_print = $outcome_field->getSetting('allowed_values')[$outcome_field->value];
      // }

      $case->form['resolve'] = \Drupal::formBuilder()->getForm(CaseResolveForm::class);
    }

    // Resolve notes.
    // Print list value instead of list key.
    $outcome_field = $case->field_case_resolve_outcome;
    if ($outcome_field->value) {
      $case->field_case_resolve_outcome->value_print = $outcome_field->getSetting('allowed_values')[$outcome_field->value];
    }

    $close_type_field = $case->field_case_resolve_close_type;
    if ($close_type_field->value) {
      $case->field_case_resolve_close_type->value_print = $close_type_field->getSetting('allowed_values')[$close_type_field->value];
    }

    if ($case->field_case_deadline->value) {
      $case->field_case_deadline->remaining = '';
      if ($case->field_case_status->value >= 10 and !empty($case->field_case_deadline->value)) {
        $days_remaining = ceil((strtotime($case->field_case_deadline->value) - REQUEST_TIME) / 86400);
        if ($days_remaining >= 0 and $days_remaining <= 15) {
          $days_remaining = $days_remaining ? $days_remaining : 0; // To avoid -0
          $case->field_case_deadline->remaining = '-- <span class="days-remaining">'
            . \Drupal::translation()->formatPlural(
              $days_remaining,
              '1 day remaining',
              '@count days remaining')->__toString()
            . '</span>';
        }
      }
    }

    return [
      '#theme' => $template,
      '#case' => $case,
    ];
  }

  protected function requesterInfo($case) {
    $users = [];
    foreach (Pbow::requestedUsers($case) as $user) {
      $flag = Pbow::userHasRequested($case, $user);

      $users[] = [
        'name' => $user->getUsername(),
        'date' => date('m/d/Y', $flag->created->value),
        'info' => json_encode([
          'uid'   => $user->id(),
          'name'  => $user->getUsername(),
          'since' => date('m/d/Y', $user->created->value),
          'email' => $user->getEmail(),
          'picture'   => Pbow::userPictureUrl($user),
          'requested' => Pbow::requestCount($user),
          'assigned'  => Pbow::assignCount($user),
          'resolved'  => Pbow::completeCount($user),
        ]),
      ];
    }

    return $users;
  }
}
