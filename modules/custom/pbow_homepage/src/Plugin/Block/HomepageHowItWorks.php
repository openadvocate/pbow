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
 *   id = "pbow_homepagehowitworks",
 *   admin_label = @Translation("PBOW How It Works"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class HomepageHowItWorks extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'pbow_homepagehowitworks_title_string' => $this->t('A default value. This block was created at %time', array('%time' => date('c'))),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['pbow_homepagehowitworks_title_string_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Block contents'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['pbow_homepagehowitworks_title_string'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['pbow_homepagehowitworks_title_string']
      = $form_state->getValue('pbow_homepagehowitworks_title_string_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->configuration['pbow_homepagehowitworks_title_string'],
    );
  }


}