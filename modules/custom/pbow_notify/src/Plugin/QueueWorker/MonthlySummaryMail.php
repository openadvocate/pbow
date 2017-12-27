<?php

/**
 * @file
 * Contains \Drupal\pbow_notify\Plugin\QueueWorker\MonthlySummaryMail.
 */

namespace Drupal\pbow_notify\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\user\Entity\User;

/**
 * Send monthly summary notifications to users.
 *
 * @QueueWorker(
 *   id = "pbow_monthly_summary_mail",
 *   title = @Translation("Monthly summary notification"),
 *   cron = {"time" = 150}
 * )
 */
class MonthlySummaryMail extends AlertMailBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($uid) {
    if ($this->currentCount() > static::CRON_LIMIT) {
      throw new SuspendQueueException;
    }

    pbow_notify_send_mail([
      'petid' => PET_ID_MONTHLY,
      'user' => User::load($uid),
      'nid' => NULL,
      'log' => "Sent monthly summary mail to %name.",
    ]);
  }
}
