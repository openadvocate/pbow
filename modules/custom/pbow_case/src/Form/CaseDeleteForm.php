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
 * Class CaseDeleteForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseDeleteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#attributes' => [
        'class' => ['btn-danger', 'pull-right'],
      ],
      '#icon' => [
        '#type' => 'html_tag',
        '#tag' => 'i',
        '#value' => '',
        '#attributes' => [
          'class' => ['fa', 'fa-trash'],
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

    $case->delete();

    drupal_set_message('Deleted case permanently: ' . $case->title->value . ' (' . $case->id() . ')');

    $form_state->setRedirect('view.case_management.page1_incoming');
  }

}
