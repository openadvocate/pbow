<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseRequestForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseRequestForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseRequestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_request_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('I Want To Take This Case'),
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
      ]
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $case = \Drupal::routeMatch()->getParameter('node');

    Pbow::requestCase($case);
  }

}
