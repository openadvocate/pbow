<?php

/**
 * @file
 * Contains \Drupal\pbow_notify\Controller\ApiController.
 */

namespace Drupal\pbow_notify\Controller;

use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiController.
 *
 * @package Drupal\pbow_notify\Controller
 */
class ApiController extends ControllerBase {
  /**
   * @return json
   *   Return boolean.
   */
  public function hasNewNotices() {
    $user = \Drupal::currentUser();

    if ($user->id()) {
      $user = User::load($user->id());
      $notices = $user->field_notices->getValue();

      $last_notice = array_pop($notices);
      
      $has_new = substr($last_notice['value'], 0, 3) == 'new';
    }

    return new JsonResponse(isset($has_new) ? $has_new : FALSE);
  }

  /**
   * @return json
   *   Return array of notices.
   */
  public function getNotices() {
    $user = \Drupal::currentUser();

    if ($user->id()) {
      $user = User::load($user->id());
      $notices = $user->field_notices->getValue();

      $notices = array_slice($notices, -10);
      $notices = array_reverse($notices);

      $formatter = \Drupal::service('date.formatter');

      foreach ($notices as $key => $notice) {
        $parts = explode("\n", $notice['value']);
        $notices[$key] = [
          'status' => $parts[0],
          'title'  => $parts[1],
          'time'   => $formatter->formatInterval(REQUEST_TIME - $parts[2], 1) . ' ago'
        ];
      }

      $this->updateNoticeStatusToOld($user);
    }

    return new JsonResponse(!empty($notices) ? $notices : []);
  }

  private function updateNoticeStatusToOld($user) {
    $notices = $user->field_notices->getValue();
    $updated = FALSE;

    foreach ($notices as $key => $notice) {
      if (strpos($notice['value'], 'new') === 0) {
        $notices[$key]['value'] = 'old' . substr($notice['value'], 3);
        $updated = TRUE;
      }
    }

    if ($updated) {
      $user->field_notices->setValue($notices);
      $user->save();
    }
  }

}
