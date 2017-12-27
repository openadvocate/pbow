<?php

/**
 * @file
 * Contains \Drupal\pbow_notify\Plugin\QueueWorker\DailyAlertMail.
 */

namespace Drupal\pbow_notify\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\user\Entity\User;

/**
 * Send daily alert notifications to users.
 *
 * @QueueWorker(
 *   id = "pbow_daily_alert_mail",
 *   title = @Translation("Daily alert"),
 *   cron = {"time" = 150}
 * )
 */
class DailyAlertMail extends AlertMailBase {
  /**
   * {@inheritdoc}
   */
  public function processItem($uid) {
    $user = User::load($uid);

    if (pbow_notify_user_has_machted_cases_today($user)) {
      if ($this->currentCount() > static::CRON_LIMIT) {
        throw new SuspendQueueException;
      }

      pbow_notify_send_mail([
        'petid' => PET_ID_DAILY,
        'user' => $user,
        'nid' => NULL,
        'log' => "Sent daily alert mail to %name.",
      ]);
    }
  }
}
