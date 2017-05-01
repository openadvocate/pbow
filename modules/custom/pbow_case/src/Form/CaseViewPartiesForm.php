<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseViewPartiesForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseViewPartiesForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseViewPartiesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'case_view_parties_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Hack to scroll down to submit button in case body is long.
    $form['#action'] = \Drupal::request()->getRequestUri() . '#state';

    $form['agree'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('I Agree with terms of this website.'),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('View Party Names'),
      '#attributes' => [
        'class' => ['btn-primary'],
      ],
      '#states' => [
        'enabled' => [
          ':checkbox#edit-agree' => ['checked' => TRUE]
        ]
      ],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $_SESSION['agreed_to_terms'] = TRUE;
  }

}
