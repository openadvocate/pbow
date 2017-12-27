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
  $user['cases-matched-today'] = [
    'name' => t('Cases (matched today)'),
    'description' => t("Cases that match user tags today."),
  ];

  $user['cases-monthly-summary'] = [
    'name' => t('Cases (monthly summary)'),
    'description' => t("Monthly summary for each subscriber."),
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
        case 'cases-matched-today':
          $replacements[$original] = _pbow_tokens_cases_matched_today($user);
          break;
        case 'cases-monthly-summary':
          $replacements[$original] = _pbow_tokens_cases_monthly_summary($user);
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
      $list[] = '- ' . $case->field_case_id->value . ': ' . $case->title->value
        . ' (' . _pbow_notify_case_link($case->id()) . ')';
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

      $list[] =  '- ' . $case->field_case_id->value . ': ' . $case->title->value
        . ' (' . _pbow_notify_case_link($case->id()) . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}

function _pbow_tokens_cases_matched($user) {
  $nids = _pbow_tokens_matched_case_nids($user);

  $list = [];
  if (!empty($nids)) {
    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      $list[] =  '- ' . $case->field_case_id->value . ': ' . $case->title->value
        . ' (' . _pbow_notify_case_link($case->id()) . ')';
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
      $list[] = '- ' . $case->field_case_id->value . ': ' . $case->title->value
        . ' (' . _pbow_notify_case_link($case->id()) . ')';
    }
  }
  else {
    $list[] = '-- No entry --';
  }

  return join("\n", $list);
}

function _pbow_tokens_cases_matched_today($user) {
  $nids = _pbow_tokens_matched_case_nids($user, TRUE);

  $list = [];
  if (!empty($nids)) {
    $cases = Node::loadMultiple($nids);

    foreach ($cases as $case) {
      $counties = [];
      foreach ($case->field_county as $item) {
        if ($item->entity) {
          $counties[] = str_replace(' County', '', $item->entity->label());
        }
      }

      if (!empty($counties)) {
        $counties = "\n ("
          . \Drupal::translation()->formatPlural(count($counties), 'County', 'Counties')
          . ': ' . join(', ', $counties) . ')';
      }
      else {
        $counties = '';
      }

      $list[] =  '- ' . $case->field_case_id->value . ': ' . $case->title->value
        . ' (' . _pbow_notify_case_link($case->id()) . ')' . $counties;
    }
  }

  return !empty($list) ? join("\n\n", $list) : NULL;
}

function _pbow_tokens_cases_monthly_summary($user) {
  static $all_cases, $counties, $tags;

  $output = [];

  if (!isset($all_cases)) {
    $all_cases = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'case')
      ->condition('field_case_status', [Pbow::AVAILABLE, Pbow::REQUESTED], 'IN')
      ->count()->execute();
  }

  $output[] = "There are currently {$all_cases} pro bono opportunities available in Idaho.";

  $matching_cases = count(_pbow_tokens_matched_case_nids($user));

  $output[] = "{$matching_cases} available opportunities match your selected tags.";

  if (!isset($counties)) {
    $counties = db_query(
     "SELECT DISTINCT REPLACE(t.name, ' County', '')
      FROM taxonomy_term_field_data t
        JOIN node__field_county c ON t.tid = c.field_county_target_id
        JOIN node__field_case_status s ON c.entity_id = s.entity_id
      WHERE s.field_case_status_value IN (20, 30)
      ORDER BY t.name"
    )->fetchCol();
  }

  $output[] = 'Pro bono opportunities are available in ' . join(', ', $counties) . ' counties.';

  if (!isset($tags)) {
    $tags = db_query(
     "SELECT DISTINCT t.name
      FROM taxonomy_term_field_data t
        JOIN node__field_case_type ct ON t.tid = ct.field_case_type_target_id
        JOIN node__field_case_status s ON ct.entity_id = s.entity_id
      WHERE s.field_case_status_value IN (20, 30)
      ORDER BY t.name"
    )->fetchCol();
  }

  $output[] = 'Currently available opportunities cover ' . join(', ', $tags) . '.';

  return join("\n\n", $output);
}

function _pbow_tokens_matched_case_nids($user, $daily = FALSE) {
  $user_counties = array_map(function ($val) {
    return $val['target_id'];
  }, $user->field_county->getValue());

  $user_populations = array_map(function ($val) {
    return $val['target_id'];
  }, $user->field_population->getValue());

  $user_case_types = array_map(function ($val) {
    return $val['target_id'];
  }, $user->field_case_type->getValue());

  if ($user_counties and ($user_populations or $user_case_types)) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'case')
      ->condition('field_case_status', [Pbow::AVAILABLE, Pbow::REQUESTED], 'IN');

    if ($daily) {
      $query->condition('field_case_time_available', [gmdate('Y-m-d\TH:i:s', strtotime('yesterday')), gmdate('Y-m-d\TH:i:s', strtotime('today'))], 'BETWEEN');
    }

    // Match cases: county AND (population OR type)
    $query->condition('field_county', $user_counties, 'IN');

    $group = $query->orConditionGroup();
    if ($user_populations) {
      $group->condition('field_population', $user_populations, 'IN');
    }
    if ($user_case_types) {
      $group->condition('field_case_type', $user_case_types, 'IN');
    }
    $query->condition($group);

    return $query->execute();
  }

  return [];
}

function pbow_notify_user_has_machted_cases_today($user) {
  $nids = _pbow_tokens_matched_case_nids($user, TRUE);

  return count($nids);
}

/**
 * Return link to Case.
 */
function _pbow_notify_case_link($nid) {
  return \Drupal::url('entity.node.canonical', ['node' => $nid], ['absolute' => TRUE]);
}
