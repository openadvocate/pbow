<?php

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\Entity\Node;
use Drupal\pbow_case\Pbow;

/**
  * Implements hook_token_info().
  */
function pbow_notify_token_info() {
  $user['cases-assigned'] = [
    'name' => t('Cases (assigned)'),
    'description' => t("Cases assigned to user."),
  ];
  $user['cases-requested'] = [
    'name' => t('Cases (requested)'),
    'description' => t("Pending Cases that user requested."),
  ];
  $user['cases-matched'] = [
    'name' => t('Cases (matched)'),
    'description' => t("Cases that match user tags."),
  ];
  $user['cases-completed'] = [
    'name' => t('Cases (completed)'),
    'description' => t("Cases that user completed."),
  ];

  return [
    'tokens' => [ 'user' => $user ],
  ];
}

 /**
  * Implements hook_tokens().
  */
function pbow_notify_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'user' and !empty($data['user'])) {
    $user = $data['user'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'cases-assigned':
          $replacements[$original] = _pbow_tokens_cases_assigned($user);
          break;
        case 'cases-requested':
          $replacements[$original] = _pbow_tokens_cases_requested($user);
          break;
        case 'cases-matched':
          $replacements[$original] = _pbow_tokens_cases_matched($user);
          break;
        case 'cases-completed':
          $replacements[$original] = _pbow_tokens_cases_completed($user);
          break;
      }
    }
  }

  return $replacements;
}

/*
 * Logic of these helper functions borrowed from Dashboard blocks.
 */

function _pbow_tokens_cases_assigned($user) {
  $nids = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'case')
    ->condition('field_case_status', Pbow::ASSIGNED)
    ->condition('field_case_assigned', $user->id())
    ->execute();

  $list = [];
  if (!empty($nids)) {
    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      $list[] = '- ' . $case->field_case_id->value . ': ' . $case->title->value . ' ('
        . \Drupal::url('entity.node.canonical', ['node' => $case->id()], ['absolute' => TRUE])
        . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}

function _pbow_tokens_cases_requested($user) {
  $flag_service = \Drupal::service('flag');
  $flag = $flag_service->getFlagById('case_request');
  $list = [];

  if ($flaggings = $flag_service->getFlagFlaggings($flag, $user)) {
    $nids = array_map(function($val) {
      return $val->entity_id->value;
    }, $flaggings);

    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      if ($case->field_case_assigned->target_id == $user->id()) {
        continue;
      }

      $list[] =  '- ' . $case->field_case_id->value . ': ' . $case->title->value . ' ('
        . \Drupal::url('entity.node.canonical', ['node' => $case->id()], ['absolute' => TRUE])
        . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}

function _pbow_tokens_cases_matched($user) {
  $flag_service = \Drupal::service('flag');
  $flag = $flag_service->getFlagById('case_request');

  if ($flaggings = $flag_service->getFlagFlaggings($flag, $user)) {
    $requested_nids = array_map(function($val) {
      return $val->entity_id->value;
    }, $flaggings);
  }

  $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'case')
    ->condition('field_case_status', [Pbow::AVAILABLE, Pbow::REQUESTED], 'IN');

  if (!empty($requested_nids)) {
    $query->condition('nid', $requested_nids, 'NOT IN');
  }

  // Match case type, population, county
  $group = $query->orConditionGroup()
    ->condition('field_population', array_map(function ($val) { return $val['target_id']; }, $user->field_population->getValue()), 'IN')
    ->condition('field_case_type',  array_map(function ($val) { return $val['target_id']; }, $user->field_case_type->getValue()), 'IN')
    ->condition('field_county',     array_map(function ($val) { return $val['target_id']; }, $user->field_county->getValue()), 'IN');
  $query->condition($group);

  $nids = $query->execute();

  if (!empty($nids)) {
    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      $list[] =  '- ' . $case->field_case_id->value . ': ' . $case->title->value . ' ('
        . \Drupal::url('entity.node.canonical', ['node' => $case->id()], ['absolute' => TRUE])
        . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}

function _pbow_tokens_cases_completed($user) {
  $nids = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'case')
    ->condition('field_case_status', Pbow::RESOLVED)
    ->condition('field_case_assigned', $user->id())
    ->execute();

  $list = [];
  if (!empty($nids)) {
    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      $list[] = '- ' . $case->field_case_id->value . ': ' . $case->title->value . ' ('
        . \Drupal::url('entity.node.canonical', ['node' => $case->id()], ['absolute' => TRUE])
        . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}
