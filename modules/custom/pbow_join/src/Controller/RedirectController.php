<?php

/**
 * @file
 * Contains \Drupal\pbow_join\Controller\RedirectController.
 */

namespace Drupal\pbow_join\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class RedirectController.
 *
 * @package Drupal\pbow_join\Controller
 */
class RedirectController extends ControllerBase {
  /**
   * @return string
   *   Return Hello string.
   */
  public function goRedirect() {
    $currentUser = \Drupal::currentUser();

    if ($currentUser->isAuthenticated()) {
      return $this->redirect('pbow_join.subscribed_tags', ['user' => $currentUser->id()]);
    }

    return $this->redirect('<front>');
  }

}
