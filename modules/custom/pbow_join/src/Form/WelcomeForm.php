<?php

/**
 * @file
 * Contains \Drupal\pbow_join\Form\WelcomeForm.
 */

namespace Drupal\pbow_join\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class WelcomeForm.
 *
 * @package Drupal\pbow_join\Form
 */
class WelcomeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pbow_welcome_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Use query with a semi random value to make sure the page is not cached.
    // Take only first 32 chars. If the page is only meant to be redirected from
    // /join page, otherwise redirect to front page.
    if (empty($_GET['result']) or substr($_GET['result'], 0, 32) != md5(\Drupal::request()->getClientIp())) {
      return new RedirectResponse('/');
    }

    $form['message'] = [
      '#markup' => <<<OUT
        <p>
          Congrats! An email has been sent to you. Follow the link in the email to reset the password.
        </p>
OUT
    ];

    $form['go_to_home'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go to home page'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('<front>');
  }

}
