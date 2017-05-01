<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseRevokeForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseRevokeForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseRevokeForm extends FormBase {

  protected $case;

  public function __construct() {
    $this->case = \Drupal::routeMatch()->getParameter('node');
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_revoke_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $this->case->field_case_assigned->target_id,
    ];

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Confirm Revoke'),
      '#attributes' => [
        'class' => ['btn-danger', 'pull-right']
      ],
      "#icon" => array(
        '#type' => "html_tag",
        '#tag' => "span",
        '#value' => "",
        '#attributes' =>  array(
            'class' => array("fa","fa-check")
        )
      )
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $revoked = $this->case->field_case_assigned->entity;

    Pbow::revokeUser($this->case, $revoked);
  }

}
