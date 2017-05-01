<?php

/**
 * @file
 * Contains \Drupal\pbow_case\Form\CaseCsvImportForm.
 */

namespace Drupal\pbow_case\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CaseCsvImportForm.
 *
 * @package Drupal\pbow_case\Form
 */
class CaseCsvImportForm extends FormBase {

  const CSV_CASE_ID   = 0;
  const CSV_TITLE     = 1;
  const CSV_BODY      = 2;
  const CSV_PARTNER   = 3;
  const CSV_CLI_NAME  = 4;
  const CSV_CLI_ALIAS = 5;
  const CSV_ADV_NAME  = 6;
  const CSV_ADV_ALIAS = 7;
  const CSV_PROB_CODE = 8;
  const CSV_DEADLINE  = 9;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pbow_case_csv_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV file'),
    ];

    $form['note'] = [
      '#type' => 'markup',
      '#markup' => '<div class="well">
        <ul>
          <li>Columns expected; Case ID, Title, Description, Partner, Client Name, Client Aliases, Adverse Party Name, Adverse Party Aliases, LSC Problem Code, Deadline.</li>
          <li>Multiple values in a field should be separated by linebreaks.</li>
          <li>Download <a href="/modules/custom/pbow_case/case-import-sample.csv">Sample CSV</a>.</li>
        </ul>
      </div>'
    ];

    $form['check_data'] = [
      '#type' => 'checkbox',
      '#title' => t('Dry run: verify uploaded data without updating database'),
      '#description' => t('Uncheck and upload again to commit the changes.'),
      '#default_value' => TRUE,
    ];

    $form['warning'] = [
      '#type' => 'item',
      '#markup' => '<div class="text-danger">Uploading CSV file will update databse. Upload with "Dry run" checked first if you have not done it already.</div>',
      '#states' => [
        'visible' => [
          ':input[name="check_data"]' => ['checked' => FALSE]
        ]
      ]
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Upload'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($_FILES['files']['name']['upload'])) {
      $form_state->setErrorByName('upload', $this->t('No file uploaded.'));
      return;
    }
    
    $file_info = file_save_upload('upload', [
      'file_validate_extensions' => ['csv'],
    ], NULL, 0, FILE_EXISTS_REPLACE);

    $csv = [];
    if ($handle = fopen($file_info->getFileUri(), "r")) {
      while ($data = fgetcsv($handle)) {
        $csv[] = $data;
      }
      fclose($handle);
    }
    else {
      $form_state->setErrorByName('upload', $this->t('Error uploading file.'));
      return;
    }

    $headers = array_shift($csv);

    if (count($headers) != 10) {
      $form_state->setErrorByName('upload', $this->t('Uploaded file does not have 10 columns expected.'));
      return;
    }

    // Check header names.
    if ($headers[0] != 'Case ID') {
      $form_state->setErrorByName('upload', $this->t('Uploaded file does not have a header row; Case ID, Title, Body, etc.'));
      return;
    }

    $this->cleanUpCsv($csv);

    $form_state->setTemporaryValue('csv', $csv);

    $this->checkCaseId($form_state);
    $this->checkValues($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('check_data')) {
      $this->createCases($form_state);
    }
    else {
      $csv = &$form_state->getTemporaryValue('csv');

      drupal_set_message(count($csv) . ' cases checked to create.');
    }
  }

  protected function cleanUpCsv(&$csv) {
    foreach ($csv as &$row) {
      for ($i = self::CSV_CASE_ID; $i <= self::CSV_DEADLINE; $i++) {
        $row[$i] = trim($row[$i]);
      }
    }
  }

  protected function checkCaseId($form_state) {
    // As csv data is read only, use reference to conserve resource.
    $csv = &$form_state->getTemporaryValue('csv');
    $messages = [];

    // Check duplicate Case IDs in CSV.
    $case_ids = array_column($csv, self::CSV_CASE_ID);

    $duplicates = array_filter(array_count_values($case_ids), function($val) {
      return $val > 1;
    });

    if (!empty($duplicates)) {
      $messages[] = 'Duplicate Case IDs found in CSV: ' . join(', ', array_keys($duplicates));
    }

    // Check case ID duplicates against DB.
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'case')
      ->condition('status', 1)
      ->condition('field_case_id', $case_ids, 'IN')
      ->execute();

    if (count($nids)) {
      $duplicates = [];
      foreach ($nids as $nid) {
        $node = \Drupal\node\Entity\Node::load($nid);
        $duplicates[] = $node->field_case_id->value;
      }
      $messages[] = 'Duplicate case IDs found in the database: ' . join(', ', $duplicates);
    }

    foreach ($messages as $key => $msg) {
      $form_state->setErrorByName("id-check-$key", $msg);
    }
  }

  protected function checkValues($form_state) {
    // As csv data is read only, use reference to conserve resource.
    $csv = &$form_state->getTemporaryValue('csv');
    $messages = [];

    foreach ($csv as $row) {
      $id = $row[self::CSV_CASE_ID];

      // Check Case ID
      if (strlen($row[self::CSV_CASE_ID]) < 1 or strlen($row[self::CSV_CASE_ID]) > 20) {
        $messages[] = "({$id}) Case ID missing or too long (max 20 chars).";
      }

      // Check Title
      if (strlen($row[self::CSV_TITLE]) < 1 or strlen($row[self::CSV_TITLE]) > 200) {
        $messages[] = "({$id}) Title missing or too long (max 200 chars).";
      }

      // Check Body
      if (strlen($row[self::CSV_BODY]) < 1 or strlen($row[self::CSV_BODY]) > 2000) {
        $messages[] = "({$id}) Body missing or too long (max 2000 chars).";
      }

      // Check Partner
      if (!$this->getTermIds($row[self::CSV_PARTNER], 'partner')) {
        $messages[] = "({$id}) Partner '{$row[self::CSV_PARTNER]}' not recognized.";
      }

      // Check Client Names
      if (strlen($row[self::CSV_CLI_NAME]) < 1 or strlen($row[self::CSV_CLI_NAME]) > 200) {
        $messages[] = "({$id}) Client Name missing or too long (max 200 chars).";
      }
      foreach ($this->getValues($row[self::CSV_CLI_ALIAS]) as $alias) {
        if (strlen($alias) > 200) {
          $messages[] = "({$id}) Client Alias too long (max 200 chars).";
        }
      }

      // Check Adverse Party Names
      if (strlen($row[self::CSV_ADV_NAME]) < 1 or strlen($row[self::CSV_ADV_NAME]) > 200) {
        $messages[] = "({$id}) Adverse Party Name missing or too long (max 200 chars).";
      }
      foreach ($this->getValues($row[self::CSV_ADV_ALIAS]) as $alias) {
        if (strlen($alias) > 200) {
          $messages[] = "({$id}) Adverse Party Alias too long (max 200 chars).";
        }
      }

      // Check LSC Problem Codes
      if (FALSE === $this->getTermIds($row[self::CSV_PROB_CODE], 'problem_code')) {
        $messages[] = "({$id}) LSC Problem Code(s) not recognized.";
      }

      // Check Deadline
      if (!empty($row[self::CSV_DEADLINE])) {
        if (strtotime($row[self::CSV_DEADLINE]) < strtotime('today')) {
          $messages[] = "({$id}) Deadline must be set in the future.";
        }
      }
    }

    foreach ($messages as $key => $msg) {
      $form_state->setErrorByName("val-check-$key", $msg);
    }
  }

  protected function createCases($form_state) {
    // As csv data is read only, use reference to conserve resource.
    $csv = &$form_state->getTemporaryValue('csv');

    $new_nids = [];

    foreach ($csv as $row) {
      $case = Node::create([
        'type'               => 'case',
        'title'              => $row[self::CSV_TITLE],
        'body'               => $row[self::CSV_BODY],
        'field_case_source'  => 'csv',
        'field_case_id'      => $row[self::CSV_CASE_ID],
        'field_case_partner' => $this->getTermIds($row[self::CSV_PARTNER], 'partner'),
        'field_case_client_name'   => $row[self::CSV_CLI_NAME],
        'field_case_client_alias'  => $this->getValues($row[self::CSV_CLI_ALIAS]),
        'field_case_adverse_name'  => $row[self::CSV_ADV_NAME],
        'field_case_adverse_alias' => $this->getValues($row[self::CSV_ADV_ALIAS]),
        'field_problem_code'  => $this->getTermIds($row[self::CSV_PROB_CODE], 'problem_code'),
        'field_case_deadline' => date('Y-m-d', strtotime($row[self::CSV_DEADLINE])),
      ]);

      $case->save();

      $new_nids[] = $case->id();
      drupal_set_message('Created case: ' . $case->title->value . ' (' . $case->id() . ')');
    }

    if (!empty($new_nids)) {
      \Drupal::logger('case_import')->notice("Cases created via CSV: " . join(', ', $new_nids));
    }
  }

  /**
   * Parse one or more lines of terms.
   * 
   * @return array of term ids or FALSE if there are unmatched terms found.
   */
  protected function getTermIds($names, $vocabulary = NULL) {
    $tids = [];

    foreach ($this->getValues($names) as $name) {
      if ($terms = taxonomy_term_load_multiple_by_name(trim($name), $vocabulary)) {
        $tids[] = key($terms);
      }
    }

    if (count($this->getValues($names)) == count($tids)) {
      return $tids;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Split (by linebreak) and trim.
   * 
   * @return array of words.
   */
  protected function getValues($str, $delimiter = "\n") {
    return array_values(array_filter(array_map('trim', explode($delimiter, $str))));
  }

}
