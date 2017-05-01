<?php 
/*
 * @file
 * Custom homepage Banner
 */

namespace Drupal\pbow_homepage\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block the Homepage Banner.
 *
 * @Block(
 *   id = "pbow_homepagestats",
 *   admin_label = @Translation("PBOW Home Page Stats"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class HomepageStats extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'pbow_homepagestats_stat1' => $this->t('794'),
      'pbow_homepagestats_stat1_text' => $this->t('Attorneys in Idaho'),


      'pbow_homepagestats_stat2' => $this->t('93'),
      'pbow_homepagestats_stat2_text' => $this->t('Cases available'),


      'pbow_homepagestats_stat3' => $this->t('945'),
      'pbow_homepagestats_stat3_text' => $this->t('Idahoans helped in 2016'),

      'pbow_homepagestats_stat0' => $this->t('91%'),
      'pbow_homepagestats_stat0_text' => $this->t('of civil cases in the Idaho Supreme Court have self-represented defendants. '),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['pbow_homepagestats_stat0'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Wide stat'),
      '#description' => $this->t('Stat number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat0'],
    );

    $form['pbow_homepagestats_stat0_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Wide stat text'),
      '#description' => $this->t('Wide stat next to number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat0_text'],
    );


    $form['pbow_homepagestats_stat1'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('First stat'),
      '#description' => $this->t('First stat number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat1'],
    );

    $form['pbow_homepagestats_stat1_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('First stat text'),
      '#description' => $this->t('First stat text below number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat1_text'],
    );



    $form['pbow_homepagestats_stat2'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Second stat'),
      '#description' => $this->t('Second stat number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat2'],
    );

    $form['pbow_homepagestats_stat2_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Second stat'),
      '#description' => $this->t('Second stat number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat2_text'],
    );



    $form['pbow_homepagestats_stat3'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Third stat'),
      '#description' => $this->t('Third stat text below number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat3'],
    );

    $form['pbow_homepagestats_stat3_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Third stat'),
      '#description' => $this->t('Third stat text below number'),
      '#default_value' => $this->configuration['pbow_homepagestats_stat3_text'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['pbow_homepagestats_stat0']
      = $form_state->getValue('pbow_homepagestats_stat0');

    $this->configuration['pbow_homepagestats_stat0_text']
      = $form_state->getValue('pbow_homepagestats_stat0_text');


    $this->configuration['pbow_homepagestats_stat1']
      = $form_state->getValue('pbow_homepagestats_stat1');

    $this->configuration['pbow_homepagestats_stat1_text']
      = $form_state->getValue('pbow_homepagestats_stat1_text');



    $this->configuration['pbow_homepagestats_stat2']
      = $form_state->getValue('pbow_homepagestats_stat2');

    $this->configuration['pbow_homepagestats_stat2_text']
      = $form_state->getValue('pbow_homepagestats_stat2_text');



    $this->configuration['pbow_homepagestats_stat3']
      = $form_state->getValue('pbow_homepagestats_stat3');

    $this->configuration['pbow_homepagestats_stat3_text']
      = $form_state->getValue('pbow_homepagestats_stat3_text');


  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => array( 
      ),
      'pbow_homepagestats_stat0' => $this->configuration['pbow_homepagestats_stat0'],
      'pbow_homepagestats_stat0_text' => $this->configuration['pbow_homepagestats_stat0_text'],

      'pbow_homepagestats_stat1' => $this->configuration['pbow_homepagestats_stat1'],
      'pbow_homepagestats_stat1_text' => $this->configuration['pbow_homepagestats_stat1_text'],

      'pbow_homepagestats_stat2' => $this->configuration['pbow_homepagestats_stat2'],
      'pbow_homepagestats_stat2_text' => $this->configuration['pbow_homepagestats_stat2_text'],

      'pbow_homepagestats_stat3' => $this->configuration['pbow_homepagestats_stat3'],
      'pbow_homepagestats_stat3_text' => $this->configuration['pbow_homepagestats_stat3_text'],

    );
  }


}