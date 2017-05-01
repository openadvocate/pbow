<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseAssignOverlapForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseAssignOverlapForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseAssignOverlapForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_assign_overlap_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['uid'] = [
      '#type' => 'hidden',
      // '#value' => '', Don't set value (even to blank) as it is set by JS.
    ];

    $form['assign'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm Assign'),
      '#attributes' => [
        'class' => ['btn-success', 'pull-right'],
      ],
      "#icon" => array(
        '#type' => "html_tag",
        '#tag' => "span",
        '#value' => "",
        '#attributes' =>  array(
            'class' => array("fa","fa-check")
        )
      )
    ];

    $form['#attached']['library'][] = 'pbow_case/pbow_case';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Assign to the requestee.
    if ($uid = $form_state->getValue('uid')) {
      $case = \Drupal::routeMatch()->getParameter('node');
      $assignee = User::load($uid);
      $assignee_name = $assignee->getUsername();
      $assigner_name = \Drupal::currentUser()->getUsername();

      $case->set('field_case_status', Pbow::ASSIGNED);
      $case->set('field_case_assigned', $uid);
      $case->set('field_case_time_assigned', Pbow::now());
      Pbow::setLog($case, ['Assigned', $assigner_name, $assignee_name]);

      $case->save();

      pbow_notify_set_notice([
        'pet'  => PET_ID_ASSIGN,
        'user' => $assignee,
        'case' => $case,
      ]);

      $this->clearRequestsAndNotifyRejects($case);
    }
  }

  private function clearRequestsAndNotifyRejects($case) {
    $assignee_uid = $case->field_case_assigned->target_id;
    $requesters = Pbow::requestedUsers($case);

    foreach ($requesters as $requester) {
      if ($requester->id() != $assignee_uid) {
        pbow_notify_set_notice([
          'pet'  => PET_ID_REJECT,
          'user' => $requester,
          'case' => $case
        ]);
      }
    }
  }

}
