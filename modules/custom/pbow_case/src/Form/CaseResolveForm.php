<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseResolveForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseResolveForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseResolveForm extends FormBase {

  protected $case;

  public function __construct() {
    $this->case = \Drupal::routeMatch()->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_resolve_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['closure_date'] = [
      '#type' => 'date',
      '#title' => 'Date of Closure',
      '#required' => TRUE,
    ];
    $form['hours_att'] = [
      '#type' => 'number',
      '#title' => 'Attorney Hours',
      '#size' => 64,
      '#min' => 0,
      '#max' => 199,
      '#required' => TRUE,
    ];
    $form['hours_para'] = [
      '#type' => 'number',
      '#title' => 'Paralegal Hours',
      '#size' => 64,
      '#min' => 0,
      '#max' => 199,
      '#required' => TRUE,
    ];
    $form['outcome'] = [
      '#type' => 'radios',
      '#title' => 'Closing Outcome',
      '#options' => $this->case->field_case_resolve_outcome->getSetting('allowed_values'),
      '#required' => TRUE,
    ];
    $form['close_type'] = [
      '#type' => 'radios',
      '#title' => 'Close Reason',
      '#options' => $this->case->field_case_resolve_close_type->getSetting('allowed_values'),
      '#required' => TRUE,
    ];
    $form['note'] = [
      '#type' => 'textarea',
      '#title' => 'Closing Notes',
      '#description' => 'Please describe status of this casse in minimum 250 characters.',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Resolve',
      '#attributes' => [
        'class' => ['btn-success', 'pull-right']
      ],
      '#icon' => [
        '#type' => 'html_tag',
        '#tag' => 'i',
        '#value' => '',
        '#attributes' => [
          'class' => ['fa', 'fa-check'],
        ]
      ],
      '#prefix' => '<div class="mt30 clearfix">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = \Drupal::currentUser()->getUsername();

    $this->case->set('field_case_status', Pbow::RESOLVED);
    $this->case->set('field_case_time_completed', Pbow::now());

    $this->case->set('field_case_resolve_date',       $form_state->getValue('closure_date'));
    $this->case->set('field_case_resolve_hours_att',  $form_state->getValue('hours_att'));
    $this->case->set('field_case_resolve_hours_para', $form_state->getValue('hours_para'));
    $this->case->set('field_case_resolve_outcome',    $form_state->getValue('outcome'));
    $this->case->set('field_case_resolve_close_type', $form_state->getValue('close_type'));
    $this->case->set('field_case_resolve_note',       $form_state->getValue('note'));

    $note = serialize([
      'hours_att'  =>  $form_state->getValue('hours_att'),
      'hours_para' =>  $form_state->getValue('hours_para'),
      'outcome'    =>  $form_state->getValue('outcome'),
      'close_type' =>  $form_state->getValue('close_type'),
      'note'       =>  $form_state->getValue('note'),
    ]);

    Pbow::setLog($this->case, ['Resolved', $name, '', $note]);

    $this->case->save();
  }
}
