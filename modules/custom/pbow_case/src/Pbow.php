<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Pbow.
 * Helper functions.
 */

namespace Drupal\pbow_case;

use Drupal\image\Entity\ImageStyle;

class Pbow {

  const INCOMING  = 10;
  const AVAILABLE = 20;
  const REQUESTED = 30;
  const ASSIGNED  = 40;
  const RESOLVED  = 50;
  const ARCHIVED  = 60;

  public static function isStaffRole() {
    return in_array('staff', \Drupal::currentUser()->getRoles());
  }

  public static function isUserRole() {
    return in_array('authenticated', \Drupal::currentUser()->getRoles());
  }

  public static function now() {
    return gmdate(DATETIME_DATETIME_STORAGE_FORMAT, REQUEST_TIME);
  }

  public static function setLog($case, $input) {
    $new = [static::now() . 'Z'];
    $new = array_merge($new, $input);
    $new += ['', '', '', '', '']; // Pad empty strings if not 5 items (csv).

    $log = trim($case->field_case_status_log->value);
    $log .= "\n" . static::putCsv($new);

    $case->field_case_status_log->value = trim($log);
  }

  /**
   * Convert list to CSV line.
   * @see https://gist.github.com/johanmeiring/2894568
   */
  public static function putCsv($input) {
    $fp = fopen('php://temp', 'r+b');
    fputcsv($fp, $input, ',', '"');
    rewind($fp);
    $data = rtrim(stream_get_contents($fp), "\n");
    fclose($fp);
    return $data;
  }

  public static function userHasConflictChecked($case, $user = NULL) {
    $user = $user ?: \Drupal::currentUser();

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('conflict_check');

    return $flag_service->getFlagging($flag, $case, $user);
  }

  public static function conflictCheckCase($case, $user = NULL) {
    $user = $user ? $user : \Drupal::currentUser();

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('conflict_check');
    $flag_service->flag($flag, $case, $user);

    Pbow::setLog($case, ['Conflict checked', $user->getUsername()]);

    $case->save();
  }

  public static function requestCase($case, $user = NULL) {
    $user = $user ? $user : \Drupal::currentUser();

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');
    $flag_service->flag($flag, $case, $user);

    // Update case status to Requested.
    if ($case->field_case_status->value == static::AVAILABLE) {
      $case->set('field_case_status', static::REQUESTED);
      $case->set('field_case_time_requested', static::now());
    }

    Pbow::setLog($case, ['Requested', $user->getUsername()]);

    $case->save();
  }

  public static function revokeUser($case, $revoked) {
    $revoker = \Drupal::currentUser();

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');
    $flag_service->unflag($flag, $case, $revoked);

    // Update case status.
    $status = count(static::requestedUsers($case)) ? static::REQUESTED : static::AVAILABLE;
    $case->set('field_case_status', $status);

    // Clear fields.
    $case->set('field_case_assigned', NULL);
    $case->set('field_case_time_assigned', NULL);
    if ($status == static::AVAILABLE) {
      $case->set('field_case_time_requested', NULL);
      $case->set('field_case_time_available', static::now());
    }

    Pbow::setLog($case, ['Revoked', $revoker->getUsername(), $revoked->getUsername()]);

    $case->save();

    pbow_notify_set_notice([
      'pet'  => PET_ID_REVOKE,
      'user' => $revoked,
      'case' => $case,
    ]);
  }

  public static function userHasRequested($case, $user = NULL) {
    $user = $user ?: \Drupal::currentUser();

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');

    return $flag_service->getFlagging($flag, $case, $user);
  }

  public static function requestedUsers($case) {
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');
    $flaggers = $flag_service->getFlaggingUsers($case, $flag);

    return $flaggers;
  }

  public static function userAgreedToTerms() {
    return !empty($_POST['agree']) and !empty($_POST['op']) and $_POST['op'] == 'View Party Names';
  }

  public static function userIsAssigned($case, $user = NULL) {
    $user = $user ?: \Drupal::currentUser();

    return $user->id() and $case->field_case_assigned->target_id == $user->id();
  }

  public static function userIsRejected($case, $user = NULL) {
    $user = $user ?: \Drupal::currentUser();

    return $user->id() and $case->field_case_assigned->target_id
       and $case->field_case_assigned->target_id != $user->id();
  }

  public static function userPictureUrl($user) {
    if (empty($user)) return NULL;

    if ($user->user_picture->isEmpty()) {
      $url = file_create_url('themes/pbow/images/default-user.png');
    }
    else {
      $uri = $user->user_picture->entity->getFileUri();
      $url = ImageStyle::load('uer_image')->buildUrl($uri);
    }

    return $url;
  }

  public static function requestCount($user) {
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('case_request');

    return count($flag_service->getFlagFlaggings($flag, $user));
  }

  public static function assignCount($user) {
    return \Drupal::database()->select('node__field_case_assigned')
      ->condition('field_case_assigned_target_id', $user->id())
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  public static function completeCount($user) {
    $query = \Drupal::database()->select('node__field_case_assigned', 'a');
    $query->join('node__field_case_status', 's', 'a.entity_id = s.entity_id');

    // Include Resolved + Archived
    return $query->condition('a.field_case_assigned_target_id', $user->id())
      ->condition('s.field_case_status_value', [Pbow::RESOLVED, Pbow::ARCHIVED], 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  public static function setUserCreatedTime($user) {
    // Initially set daily/monthly alerts on
    $user->set('field_notification', ['daily', 'monthly']);
    $user->save();

    \Drupal::database()->update('users_field_data')
      ->fields(['created' => REQUEST_TIME])
      ->condition('uid', $user->id())
      ->execute();
  }

  public static function isReportTablePath() {
    return \Drupal::request()->getpathInfo() == '/report/table';
  }
}
