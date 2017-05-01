<?php

/**
 * @file
 * Contains \Drupal\pbow_join\Form\JoinForm.
 */

namespace Drupal\pbow_join\Form;

use Drupal\pbow_case\Pbow;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class JoinForm.
 *
 * @package Drupal\pbow_join\Form
 */
class JoinForm extends FormBase {

  const PET_ID_PWD_RESET = 1; // See pbow_notify.module for PET_ID_PWD_RESET
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pbow_join_form';
  }

  public function access(AccountInterface $account) {
    return $account->id() ? AccessResult::forbidden() : AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Honey pot field
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 64,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Enter the email address you have on file with the Idaho State Bar'),
      '#maxlength' => 64,
      '#attributes' => ['required' => TRUE],
    ];
    $form['barid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your Idaho State Bar ID Number'),
      '#maxlength' => 16,
      '#attributes' => ['required' => TRUE],
    ];

    if ($terms = $this->terms()) {
      $form['terms'] = [
        '#type' => 'markup',
        '#title' => $this->t('Terms of Use'),
        '#markup' => $terms,
        '#prefix' => '<div class="terms-of-use"><h2 class="title">Terms of Use</h2><div class="terms-textarea"><div class="terms-textarea-inside">',
        '#suffix' => '</div></div></div>',
      ];
      $form['agree'] = [
        '#type' => 'checkbox',
        '#prefix' => '<div class="term-agree-list">By clicking the "I Agree" checkbox, you agree:
          <ul>
            <li>I have read the Use Agreement for Lawyers and I understand the terms of the Use Agreement.</li>
            <li>The information that I will provide is true and correct.</li>
            <li>If I do not agree to the Use Agreement for Lawyers, I will not be able to use the system.</li>
          </ul>
        </div>',
        '#title' => 'I Agree to the Terms of Use',
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Activate my Account'),
        '#attributes' => array('class'=> array('btn-primary')),
        '#states' => [
          'enabled' => [
            ':input#edit-agree' => ['checked' => TRUE]
          ]
        ],
      ];
    }
    else {
      drupal_set_message('Terms missing. Please contact site admin.', 'error');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If name field is entered, it is a bot submission as the field is not visible to human.
    if (!empty($form_state->getValue('name'))) {
      return;
    }

    if (empty($form_state->getValue('agree'))) {
      $form_state->setErrorByName('agree', 'You need to agree to the Terms of Use to activate your account.');
      return;
    }

    // Check email and bar id.
    $uids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('mail', $form_state->getValue('email'))
      ->condition('field_bar_id', $form_state->getValue('barid'))
      ->execute();

    if (empty($uids)) {
      $form_state->setErrorByName('', 'No user accounts found matching the email and Idaho State Bar ID Number. Contact site admin to join the site.');
      return;
    }

    $account = User::load(reset($uids));

    if (!empty($account->login->value)) {
      $form_state->setErrorByName('', t('This account has already been activated. Do you want to @reset?', ['@reset' => \Drupal::l('reset password', \Drupal\Core\Url::fromRoute('user.pass'))]));
      return;
    }

    // Update created time (Member since for).
    Pbow::setUserCreatedTime($account);

    $pet = pet_load(self::PET_ID_PWD_RESET);
    $params = [
      'pet_from' => \Drupal::config('system.site')->get('mail'),
      'pet_to' => $account->getEmail(),
      'pet_uid' => $account->id(),
    ];

    $mail = pet_send_one_mail($pet, $params);

    if (!empty($mail)) {
      $this->logger('pbow_join')->notice('Password reset instructions mailed to %name at %email.', array('%name' => $account->getUsername(), '%email' => $account->getEmail()));
    }
    else {
      $form_state->setErrorByName('', 'An error ocurred during sending email. Please try again or contact site admin if it persists.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Use query with a semi random value to make sure the page is not cached.
    $result = md5(\Drupal::request()->getClientIp()) . md5(REQUEST_TIME);
    $form_state->setRedirect('pbow_join.welcome', compact('result'));
  }

  private function terms() {
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/terms');
    $path = explode('/node/', $path);

    $terms = '';
    if (!empty($path[1]) and is_numeric($path[1]) and $node = Node::load($path[1])) {
      $terms = $node->body->value;
    }

    return $terms;
  }
}
