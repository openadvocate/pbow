<?php 
/*
 * @file
 * Custom homepage quote
 */

namespace Drupal\pbow_homepage\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block the Homepage quote.
 *
 * @Block(
 *   id = "pbow_homepagequote",
 *   admin_label = @Translation("PBOW Quote Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class HomepageQuote extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'pbow_homepagequote_quote_string' => $this->t('Place Quote here'),
      'pbow_homepagequote_by_line_string' => $this->t('Place By Line here'),
      'pbow_homepagequote_by_line_string2' => $this->t('Place By Line 2 here'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['pbow_homepagequote_quote_string'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Quote'),
      '#description' => $this->t(' '),
      '#default_value' => $this->configuration['pbow_homepagequote_quote_string'],
    );

    $form['pbow_homepagequote_by_line_string'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('By line'),
      '#description' => $this->t(' '),
      '#default_value' => $this->configuration['pbow_homepagequote_by_line_string'],
    );

    $form['pbow_homepagequote_by_line_string2'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('By line 2'),
      '#description' => $this->t(' '),
      '#default_value' => $this->configuration['pbow_homepagequote_by_line_string2'],
    );

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['pbow_homepagequote_quote_string']
      = $form_state->getValue('pbow_homepagequote_quote_string');

    $this->configuration['pbow_homepagequote_by_line_string']
      = $form_state->getValue('pbow_homepagequote_by_line_string');

    $this->configuration['pbow_homepagequote_by_line_string2']
      = $form_state->getValue('pbow_homepagequote_by_line_string2');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => array(),
      'pbow_homepagequote_quote_string' => $this->configuration['pbow_homepagequote_quote_string'],
      'pbow_homepagequote_by_line_string' => $this->configuration['pbow_homepagequote_by_line_string'],
      'pbow_homepagequote_by_line_string2' => $this->configuration['pbow_homepagequote_by_line_string2'],

    );
  }


}