<?php

/**
 * @file
 * Contains \Drupal\pbow_notify\Plugin\QueueWorker\AlertMailBase.
 */

namespace Drupal\pbow_notify\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\user\Entity\User;

abstract class AlertMailBase extends QueueWorkerBase {
  // Set a limit not to exceed hourly quota of Mailgun (100).
  const CRON_LIMIT = 50;

  abstract public function processItem($uid);

  protected function currentCount() {
    // By only checking entry for the last 1 minute, it does not matter if there
    // was a leftover from previous cron task (perhaps an hour ago).
    $count = (int)db_select('semaphore', 's')
      ->fields('s', ['value'])
      ->condition('s.name', 'pbow.alert.count')
      ->condition('s.expire', REQUEST_TIME - 60, '>')
      ->execute()
      ->fetchField();

    $count++;

    db_merge('semaphore')
      ->key(['name' =>'pbow.alert.count'])
      ->fields([
          'value' => $count,
          'expire' => REQUEST_TIME
      ])
      ->execute();

    return $count;
  }
}
