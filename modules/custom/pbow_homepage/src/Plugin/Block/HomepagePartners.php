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
 *   id = "pbow_partner",
 *   admin_label = @Translation("PBOW Home Page Partners"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class HomepagePartners extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      // 'pbow_partner_1' => $this->t('794'),
      'pbow_partner_1_text' => $this->t('Idaho Legal Aid Services'),
      'pbow_partner_2_text' => $this->t('Idaho Volunteer Lawyers Program'),
      'pbow_partner_3_text' => $this->t('Legal Services Corporation Pro Bono Innovation grant'),

    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // $form['pbow_partner_1'] = array(
    //   '#type' => 'image_button',
    //   '#title' => $this->t('Partner Logo'),
    //   '#description' => $this->t('Partner Logo'),
    //   '#default_value' => $this->configuration['pbow_partner_1'],
    // );

    $form['pbow_partner_1_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Partner 1 Label'),
      '#description' => $this->t('Partner Label'),
      '#default_value' => $this->configuration['pbow_partner_1_text'],
    );

    $form['pbow_partner_2_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Partner 2 Label'),
      '#description' => $this->t('Partner Label'),
      '#default_value' => $this->configuration['pbow_partner_2_text'],
    );

    $form['pbow_partner_3_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Partner 3 Label'),
      '#description' => $this->t('Partner Label'),
      '#default_value' => $this->configuration['pbow_partner_3_text'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    // $this->configuration['pbow_partner_1']
    //   = $form_state->getValue('pbow_partner_1');

    $this->configuration['pbow_partner_1_text']
      = $form_state->getValue('pbow_partner_1_text');

    $this->configuration['pbow_partner_2_text']
      = $form_state->getValue('pbow_partner_2_text');

    $this->configuration['pbow_partner_3_text']
      = $form_state->getValue('pbow_partner_3_text');

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(

      '#type' => 'markup',
      '#markup' => array( 
      ),

      // 'pbow_partner_1' => $this->configuration['pbow_partner_1'],
      'pbow_partner_1_text' => $this->configuration['pbow_partner_1_text'],
      'pbow_partner_2_text' => $this->configuration['pbow_partner_2_text'],
      'pbow_partner_3_text' => $this->configuration['pbow_partner_3_text'],

    );
  }


}