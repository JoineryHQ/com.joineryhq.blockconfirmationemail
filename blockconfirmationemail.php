<?php

require_once 'blockconfirmationemail.civix.php';
// phpcs:disable
use CRM_Blockconfirmationemail_ExtensionUtil as E;
// phpcs:enable

/**
 * A string to be used as a marker in all subject lines for mailing_component
 * entities which should be blocked during sending. 
 * 
 * This string aims to be 
 * - unlikely to be entered manually through the UI (to avoid false positives)
 * - hard to see (to avoid confusing the end user).
 * As such, it consists of 8 "Zero Width No-Break Space" characters, a.k.a "BOM"
 * (see https://en.wikipedia.org/wiki/Byte_order_mark). 1 would probably be sufficient,
 * but why not 8?
 */
const BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER = "\u{FEFF}\u{FEFF}\u{FEFF}\u{FEFF}\u{FEFF}\u{FEFF}\u{FEFF}\u{FEFF}";

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
      ];
      $query = "
        UPDATE civicrm_mailing_component
        SET subject = CONCAT(subject, %1)
        WHERE
          id = %2
          AND RIGHT(subject, 8) != %1
      ";
      CRM_Core_DAO::executeQuery($query, $queryParams);
    }
  }
}

/**
 * Implements hook_civicrm_alterMailParams().
 */
function blockconfirmationemail_civicrm_alterMailParams(&$params, $context) {  
  // If the subject ends with our marker, assume the email is an automated message
  // of one of the blocked types, and tell $params to abort sending.
  if (mb_substr($params['subject'], -8) == BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER) {
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
