<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseArchiveForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\pbow_case\Pbow;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseArchiveForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseArchiveForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_archive_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['archive'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Confirm Archive'),
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
    $name = \Drupal::currentUser()->getUsername();

    $case->set('field_case_status', Pbow::ARCHIVED);
    Pbow::setLog($case, ['Archived', $name]);

    $case->save();
  }

}
