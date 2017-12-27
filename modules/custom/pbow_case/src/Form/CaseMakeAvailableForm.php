<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseMakeAvailableForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseMakeAvailableForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseMakeAvailableForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_make_available_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['make_available'] = [
      '#type' => 'submit',
      '#value' => $this->t('Make Available'),
      '#attributes' => [
        'class' => ['btn-success', 'pull-right'],
      ],
      '#icon' => [
        '#type' => 'html_tag',
        '#tag' => 'i',
        '#value' => '',
        '#attributes' => [
          'class' => ['fa', 'fa-check'],
          'aria-hidden' => 'true',
        ]
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $case = \Drupal::routeMatch()->getParameter('node');
    $user = \Drupal::currentUser()->getUsername();

    // Ignore failed check (set to pass) and make Case available
    if (!$case->field_case_check_all->value) {
      $case->set('field_case_check_all', 1);
    }

    $case->set('field_case_status', Pbow::AVAILABLE);
    $case->set('field_case_time_available', Pbow::now());
    Pbow::setLog($case, ['Available', $user]);

    $case->save();
  }

}
