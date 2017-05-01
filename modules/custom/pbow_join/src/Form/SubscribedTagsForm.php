<?php

/**
 * @file
 * Contains \Drupal\pbow_join\Form\SubscribedTagsForm.
 */

namespace Drupal\pbow_join\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SubscribedTagsForm.
 *
 * @package Drupal\pbow_join\Form
 */
class SubscribedTagsForm extends FormBase {

  protected $uid;
  protected $user;

  public function __construct() {
    $this->uid = (int)explode('/', \Drupal::service('path.current')->getPath())[2];
    $this->user = User::load($this->uid);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'subscribed_tags_form';
  }

  public function access(AccountInterface $account) {
    $currentUser = \Drupal::currentUser();

    $ok = $this->uid > 0 && ($currentUser->hasPermission('administer users') || $currentUser->id() == $this->uid);

    return $ok ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default = array_map(function($val) {
      return $val['target_id'];
    }, $this->user->field_case_type->getValue());

    $form['case_type'] = array(
      '#prefix' => '<div class="row mt20"><div class="col-sm-9"><div class="card">',
      '#type' => 'checkboxes',
      '#title' => $this->t('Case Type'),
      '#description' => $this->t(''),
      '#options' => $this->loadTerms('case_type'),
      '#default_value' => $default,
      '#suffix' => '</div></div></div>',
    );

    $default = array_map(function($val) {
      return $val['target_id'];
    }, $this->user->field_population->getValue());

    $form['population'] = array(
      '#prefix' => '<div class="row"><div class="col-sm-9"><div class="card">',
      '#type' => 'checkboxes',
      '#title' => $this->t('Population'),
      '#description' => $this->t(''),
      '#options' => $this->loadTerms('population'),
      '#default_value' => $default,
      '#suffix' => '</div></div></div>',
    );

    $default = array_map(function($val) {
      return $val['target_id'];
    }, $this->user->field_county->getValue());

    $form['county'] = array(
      '#prefix' => '<div class="row"><div class="col-sm-9"><div class="card">',
      '#type' => 'checkboxes',
      '#title' => $this->t('County'),
      '#description' => $this->t(''),
      '#options' => $this->loadTerms('county'),
      '#default_value' => $default,
      '#suffix' => '</div></div></div>',
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => array('class'=> array('btn-primary')),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $case_type = $form_state->getValue('case_type');
    $this->user->set('field_case_type', array_filter($case_type));

    $population = $form_state->getValue('population');
    $this->user->set('field_population', array_filter($population));

    $county = $form_state->getValue('county');
    $this->user->set('field_county', array_filter($county));

    $this->user->save();

    drupal_set_message('Saved subscribed tags successfully.');
  }

  private function loadTerms($vocab_id) {
    $terms = [];
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocab_id);
    foreach ($tree as $term) {
      $terms[$term->tid] = $term->name;
    }

    return $terms;
  }

}
