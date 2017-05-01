<?php

/**
 * @file
 * Contains \Drupal\pbow_user_import\Form\UserImportForm.
 */

namespace Drupal\pbow_user_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UserImportForm.
 *
 * @package Drupal\pbow_user_import\Form
 */
class UserImportForm extends FormBase {

  const CSV_BAR_ID = 0;
  const CSV_LNAME  = 1;
  const CSV_FNAME  = 2;
  const CSV_EMAIL  = 3;
  const CSV_NAME   = 4;
  const CSV_UID    = 5;

  const UPLOAD_LIMIT = 500;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pbow_user_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['upload'] = [
      '#type' => 'file',
      '#title' => t('Upload CSV file'),
      '#description' => t('Upload user account records. Expected columns with headers: │ID│Last Name│First Name│Email│'),
    ];

    $form['check_data'] = [
      '#type' => 'checkbox',
      '#title' => t('Dry run: verify uploaded data without updating database'),
      '#description' => t('Uncheck and upload again to commit the changes.'),
      '#default_value' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Upload'),
    ];

    $form['#attached']['library'][] = 'pbow_user_import/pbow_user_import';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($_FILES['files']['name']['upload'])) {
      $form_state->setErrorByName('upload', $this->t('<h3>No file uploaded.</h3>'));
      return;
    }
    
    $file_info = file_save_upload('upload', [
      'file_validate_extensions' => ['csv'],
    ], NULL, 0, FILE_EXISTS_REPLACE);


    $csv = array_map('str_getcsv', file($file_info->getFileUri()));
    $headers = array_shift($csv);

    if (count($headers) != 4) {
      $form_state->setErrorByName('upload', $this->t('<h3>Four columns (ID, Last Name, First Name, Email) expected in CSV.</h3>'));
      return;
    }

    if (count($csv) > self::UPLOAD_LIMIT) {
      $form_state->setErrorByName('upload', $this->t('<h3>Upload cannot contain more than %count records.</h3>', ['%count' => self::UPLOAD_LIMIT]));
      return;
    }

    $this->cleanUpCsv($csv);

    $form_state->setTemporaryValue('csv', $csv);

    if ($form_state->getValue('check_data')) {
      drupal_set_message(t('<h2>Dry run result. Following will be committed when you upload without checking "Dry run"</h2><h3 style="color: #a00">Errors, if any, need to be resolved to commit to the database. Errors appear in the red <img src="/core/misc/icons/e32700/error.svg"> section.</h3>'), 'warning');
    }
    else {
      drupal_set_message(t('<h2>Database commit result.</h2><h3 style="color: #a00">Upload will not be committed if there are any <img src="/core/misc/icons/e32700/error.svg"> errors.</h3>'), 'warning');
    }


    if (!$this->checkDataIntegrity($form_state)) {
      return;
    }

    $this->checkDataAgainstDb($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('check_data')) {
      $this->updateUserRecords($form_state);
    }
  }

  // Remove extra spaces and illegal chars on input csv data.
  protected function cleanUpCsv(&$csv) {
    foreach ($csv as &$row) {
      // Trim spaces.
      for ($i = 0; $i <= 3; $i++) {
        $row[$i] = trim($row[$i]);
      }

      // Remove extra spaces and illegal chars.
      $row[self::CSV_LNAME] = preg_replace('#[^\w .-]#', '', $row[self::CSV_LNAME]);
      $row[self::CSV_LNAME] = preg_replace('#\s{2,}#', ' ',  $row[self::CSV_LNAME]);
      $row[self::CSV_FNAME] = preg_replace('#[^\w .-]#', '', $row[self::CSV_FNAME]);
      $row[self::CSV_FNAME] = preg_replace('#\s{2,}#', ' ',  $row[self::CSV_FNAME]);

      // Save full name for username
      $row[self::CSV_NAME] = $row[self::CSV_FNAME] . ' ' . $row[self::CSV_LNAME];
    }
  }

  /**
   * @return TRUE = good, FALSE = with error
   */
  protected function checkDataIntegrity($form_state) {
    $csv = &$form_state->getTemporaryValue('csv');
    $messages = [];

    // Check duplicate IDs in CSV.
    $bar_ids = array_column($csv, self::CSV_BAR_ID);
    $duplicates = array_filter(array_count_values($bar_ids), function($val) {
      return $val > 1;
    });

    if (!empty($duplicates)) {
      $messages[] = 'Duplicate bar IDs found in CSV: ' . implode(', ', array_keys($duplicates));
    }

    // Check duplicate emails in CSV.
    $bar_emails = array_column($csv, self::CSV_EMAIL);
    $duplicates = array_filter(array_count_values($bar_emails), function($val) {
      return $val > 1;
    });

    if (!empty($duplicates)) {
      $messages[] = 'Duplicate emails found in CSV: ' . implode(', ', array_keys($duplicates));
    }

    // Check duplicate names in CSV.
    $csv_copy = $csv;
    foreach ($csv as &$row1) {
      foreach ($csv_copy as $row2) {
        if ($row1[self::CSV_BAR_ID] == $row2[self::CSV_BAR_ID]) break;

        if ($row1[self::CSV_NAME] == $row2[self::CSV_NAME]) {
          drupal_set_message("User name '{$row1[self::CSV_NAME]}' is not unique and ID will be appended.", 'warning');

          $row1[self::CSV_NAME] .= ' ' . $row1[self::CSV_BAR_ID];
        }
      }
    }

    // Check validaty of data
    foreach ($csv as $row) {
      if (!valid_email_address($row[self::CSV_EMAIL])) {
        $line = '│' . $row[self::CSV_BAR_ID] . '│' . $row[self::CSV_NAME] . '│' . $row[self::CSV_EMAIL] . '│';
        $messages[] = 'Invalid email in: ' . $line;
      }
    }

    foreach ($messages as $key => $msg) {
      $form_state->setErrorByName("dummy-$key", t('<h3>'.$msg.'</h3>'));
    }

    return $messages ? FALSE : TRUE;
  }

  /**
   * @return TRUE = good, FALSE = with error
   */
  protected function checkDataAgainstDb($form_state) {
    $csv = &$form_state->getTemporaryValue('csv');
    $messages = ['unchanged' => 0];

    foreach ($csv as &$row) { // Using reference to update 4th column.
      $unique = TRUE;
      $user_ids = \Drupal::entityQuery('user')
        ->condition('field_bar_id.value', $row[self::CSV_BAR_ID])
        ->execute();

      $user_id = count($user_ids) ? reset($user_ids) : 0;

      // Check drupal email duplicates
      $query = \Drupal::entityQuery('user');
      $or_group = $query->orConditionGroup()
        ->condition('field_bar_id.value', $row[self::CSV_BAR_ID], '<>')
        ->condition('field_bar_id.value', NULL, 'IS NULL');

      $or_group2 = $query->orConditionGroup()
        ->condition('mail.value', $row[self::CSV_EMAIL])
        ->condition('field_bar_email.value', $row[self::CSV_EMAIL]);
      
      $duplicate_exists = (bool)$query->condition($or_group)
        ->condition($or_group2)
        ->count()->execute();

      if ($duplicate_exists) {
        $messages['email'][] = $row[self::CSV_EMAIL];
        $unique = FALSE;
      }

      // Check name duplicates
      $query = \Drupal::entityQuery('user');
      $or_group = $query->orConditionGroup()
        ->condition('field_bar_id.value', $row[self::CSV_BAR_ID], '<>')
        ->condition('field_bar_id.value', NULL, 'IS NULL');
      
      $duplicate_exists = (bool)$query->condition($or_group)
        ->condition('name.value', $row[self::CSV_NAME])
        ->count()->execute();

      if ($duplicate_exists) {
        drupal_set_message("User name '{$row[self::CSV_NAME]}' is not unique and ID will be appended.", 'warning');

        $row[self::CSV_NAME] .= ' ' . $row[self::CSV_BAR_ID];
      }

      if ($user_id) {
        $user = \Drupal\user\Entity\User::load($user_id);
        $change = [];

        if ($user->name->value != $row[self::CSV_NAME]) {
          $change[] = 'Name (' . $user->name->value . ' => ' . $row[self::CSV_NAME] . ')';
        }

        if ($user->field_bar_email->value != $row[self::CSV_EMAIL]) {
          $change[] = 'Email (' . $user->field_bar_email->value . ' => ' . $row[self::CSV_EMAIL] . ')';
        }

        if (!$user->status->value) {
          $change[] = 'Status (blocked => active)';
        }

        if ($change) {
          $messages['update'][] = 'ID: ' . $row[self::CSV_BAR_ID] . ' - ' . join(' - ', $change);
          $row[self::CSV_UID] = $user_id;
        }
        else {
          $messages['unchanged']++;
          $row[self::CSV_UID] = 'unchanged';
        }
      }
      elseif ($unique) {
        $messages['new'][] = 'ID: ' . $row[self::CSV_BAR_ID] . ' - '
                           . $row[self::CSV_NAME] . ' - '
                           . $row[self::CSV_EMAIL];

        $row[self::CSV_UID] = 'new';
      }
    }

    if (!empty($messages['email'])) {
      $form_state->setErrorByName('dummy-1', t('<h3>Duplicate emails found in the records: ' . join(', ', $messages['email']) . '</h3>'));
      return FALSE;
    }

    if (!empty($messages['update'])) {
      drupal_set_message(t('<h3>Changed user accounts: ' . count($messages['update']) . ' records</h3>'), 'warning');

      foreach ($messages['update'] as $msg) {
        drupal_set_message(' - ' . $msg, 'warning');
      }
    }

    if (!empty($messages['new'])) {
      drupal_set_message(t('<h3>New user accounts: ' . count($messages['new']) . ' records</h3>'), 'warning');

      foreach ($messages['new'] as $msg) {
        drupal_set_message(' - ' . $msg, 'warning');
      }
    }

    if (!empty($messages['unchanged'])) {
      drupal_set_message(t('<h3>Unchanged user accounts: ' . $messages['unchanged'] . ' records</h3>'), 'warning');
    }

    return TRUE;
  }

  protected function updateUserRecords($form_state) {
    $csv = &$form_state->getTemporaryValue('csv');

    $result = [];
    foreach ($csv as $row) {
      if ($row[self::CSV_UID] == 'new') {
        $user = \Drupal\user\Entity\User::create([
          'name'   => $row[self::CSV_NAME],
          'mail'   => $row[self::CSV_EMAIL],
          'pass'   => user_password(),
          'status' => 1,
          'field_bar_id'    => $row[self::CSV_BAR_ID],
          'field_bar_email' => $row[self::CSV_EMAIL],
        ]);

        $user->save();
        $result['new'][] = "- {$user->name->value} ({$user->uid->value})";
      }
      elseif (is_numeric($row[self::CSV_UID])) {
        $user = \Drupal\user\Entity\User::load($row[self::CSV_UID]);

        $user->set('name', $row[self::CSV_NAME]);
        $user->set('field_bar_email', $row[self::CSV_EMAIL]);
        $user->set('status', 1);

        $user->save();
        $result['updated'][] = "- {$user->name->value} ({$user->uid->value})";
      }
    }

    if (!empty($result['new'])) {
      \Drupal::logger('user_import')->notice("Users created:\n" . join("\n", $result['new']));
    }
    if (!empty($result['updated'])) {
      \Drupal::logger('user_import')->notice("Users updated:\n" . join("\n", $result['updated']));
    }
  }

}
