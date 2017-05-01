<?php 
/*
 * @file
 * Custom Case Body
 */

namespace Drupal\pbow_case\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block the Case Body
 *
 * @Block(
 *   id = "pbow_dashboardwelcome",
 *   admin_label = @Translation("PBOW Dashboard Welcome"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class DashboardWelcome extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'pbow_dashboardwelcome' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $userCurrent = \Drupal::currentUser();
    return array(
      '#type' => 'markup',
      '#markup' => $this->t("Lorem ipsum vase body"),
      'pbow_uid' => $userCurrent->id(),
    );
  }
}