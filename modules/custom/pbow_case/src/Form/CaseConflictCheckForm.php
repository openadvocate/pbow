<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseConflictCheckForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pbow_case\Pbow;

/**
 * Class CaseConflictCheckForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseConflictCheckForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_conflict_check_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Hack to scroll down to submit button in case body is long.
    $form['#action'] = \Drupal::request()->getRequestUri() . '#state';

    $form['conflict_check'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('I have no conflict of interests in taking this case.'),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('View Case Details'),
      '#attributes' => [
        'class' => ['btn-primary'],
      ],
      '#states' => [
        'enabled' => [
          ':checkbox#edit-conflict-check' => ['checked' => TRUE]
        ]
      ],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $case = \Drupal::routeMatch()->getParameter('node');

    Pbow::conflictCheckCase($case);
  }

}
