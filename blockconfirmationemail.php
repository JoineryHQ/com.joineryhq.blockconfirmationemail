<?php

require_once 'blockconfirmationemail.civix.php';
// phpcs:disable
use CRM_Blockconfirmationemail_ExtensionUtil as E;
// phpcs:enable

/**
 * A string to be used as a marker in all subject lines for mailing_component
 * entities which should be blocked during sending.
 */
const BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER = "{BLOCKCONFIRMATIONEMAIL_DONOT_SEND}";

/**
 * Implementation of hook_civicrm_alterTemplateFile().
 */
function blockconfirmationemail_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  if ($tplName == "CRM/Mailing/Page/Component.tpl") {
    // For the Headers, Footers, and Automated Messages page, add some help text
    // at the top expaining the BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER in subject lines.
    $ext = CRM_Extension_Info::loadFromFile(E::path('info.xml'));
    $args = [
      '%1' => BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER,
      '%2' => $ext->label,
    ];
    $message = E::ts('Automated Message templates of types OptOut, Unsubscribe and Resubscribe have the marker <span style="white-space: nowrap">"%1"</span> appended to their Subject line by the <em>%2</em> extension. The extension will prevent sending of emails with this marker in the Subject line.', $args);
    CRM_Core_Session::setStatus($message, '', 'no-popup help');
  }
}

/**
 * Implementation of hook_civicrm_check().
 *
 * Add a check to the status page/System.check to report status of mailing component templates.
 */
function blockconfirmationemail_civicrm_check(&$messages, $statusNames, $includeDisabled) {

  // Early return if $statusNames doesn't call for our check
  if ($statusNames && !in_array('blockconfirmationemail_marked_subjects', $statusNames)) {
    return;
  }

  // If performing your check is resource-intensive, consider bypassing if disabled
  if (!$includeDisabled) {
    $disabled = \Civi\Api4\StatusPreference::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('is_active', '=', FALSE)
      ->addWhere('domain_id', '=', 'current_domain')
      ->addWhere('name', '=', 'blockconfirmationemail_marked_subjects')
      ->execute()->count();
    if ($disabled) {
      return;
    }
  }

  $ext = CRM_Extension_Info::loadFromFile(E::path('info.xml'));
  
  $queryParams = [
    '1' => ['%' . BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER . '%', 'String'],    
  ];
  $query = "
    SELECT id, name, component_type, (subject like %1) as is_marked
    FROM civicrm_mailing_component
    WHERE
      component_type IN ('unsubscribe','resubscribe','optout')
  ";
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  $markedItems = $unmarkedItems = [];
  while ($dao->fetch()) {
    $item = E::ts('"%1" (component type="%3", id="%2")', ['%1' => $dao->name, '%2' => $dao->id, '%3' => $dao->component_type]);
    if ($dao->is_marked) {
      $markedItems[] = $item;
    }
    else {
      $unmarkedItems[] = $item;
    }
  }
  
  if (count($unmarkedItems)) {
    $messages[] = new CRM_Utils_Check_Message(
      'blockconfirmationemail_marked_subjects',
      E::ts('The following Automated Message templates are NOT correctly prevented from being sent by the <em>%1</em> extension. You might fix this by uninstalling and re-installing the extension.', ['1' => $ext->label]) . '<ul><li>' . implode('</li><li>', $unmarkedItems) . '</li></ul>',
      E::ts('Automated Message Templates: not flagged to prevent sending'),
      \Psr\Log\LogLevel::ERROR,
      'fa-envelope'
    );
  }
  if (count($markedItems)) {
    $messages[] = new CRM_Utils_Check_Message(
      'blockconfirmationemail_marked_subjects',
      E::ts('The following Automated Message templates are prevented from being sent by the <em>%1</em> extension. This is by design.', ['1' => $ext->label]) . '<ul><li>' . implode('</li><li>', $markedItems) . '</li></ul>',
      E::ts('Automated Message Templates: flagged to prevent sending'),
      \Psr\Log\LogLevel::INFO,
      'fa-envelope'
    );
  }
}

/**
 * Implements hook_civicrm_postSave_civicrm_mailing_component().
 */
function blockconfirmationemail_civicrm_postSave_civicrm_mailing_component($dao) {
  // After a mailing_component entity has been saved:
  if ($dao->tableName() == 'civicrm_mailing_component') {
    // If the mailing_component has component_type of the relevant value:
    $blockedComponentTypes = array('unsubscribe','resubscribe','optout');
    if (in_array(strtolower($dao->component_type), $blockedComponentTypes)) {
      // Append our marker to the subject line and save via direct SQL query
      // (saving via api would create an infinite loop).
      $queryParams = [
        '1' => [BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER, 'String'],
        '2' => [$dao->id, 'String'],
        '3' => ['%' . BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER . '%', 'String'],
      ];
      $query = "
        UPDATE civicrm_mailing_component
        SET subject = CONCAT(subject, %1)
        WHERE
          id = %2
          AND subject NOT LIKE %3
      ";
      CRM_Core_DAO::executeQuery($query, $queryParams);
    }
  }
}

/**
 * Implements hook_civicrm_alterMailParams().
 */
function blockconfirmationemail_civicrm_alterMailParams(&$params, $context) {  
  // If the subject contains with our marker, assume the email is an automated message
  // of one of the blocked types, and tell $params to abort sending.
  if (strstr($params['subject'], BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER) !== FALSE) {
    CRM_Core_Error::debug_log_message("blockconfirmationemail: Blocked email to '{$params['toEmail']}', per subject '{$params['subject']}'");
    $params['abortMailSend'] = TRUE;
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function blockconfirmationemail_civicrm_config(&$config) {
  _blockconfirmationemail_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function blockconfirmationemail_civicrm_install() {
  _blockconfirmationemail_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function blockconfirmationemail_civicrm_postInstall() {
  _blockconfirmationemail_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function blockconfirmationemail_civicrm_uninstall() {
  _blockconfirmationemail_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function blockconfirmationemail_civicrm_enable() {
  _blockconfirmationemail_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function blockconfirmationemail_civicrm_disable() {
  _blockconfirmationemail_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function blockconfirmationemail_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _blockconfirmationemail_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function blockconfirmationemail_civicrm_entityTypes(&$entityTypes) {
  _blockconfirmationemail_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function blockconfirmationemail_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function blockconfirmationemail_civicrm_navigationMenu(&$menu) {
//  _blockconfirmationemail_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _blockconfirmationemail_civix_navigationMenu($menu);
//}
