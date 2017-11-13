<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'civicrm_recurring_mail.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civicrm_recurring_mail_civicrm_config(&$config) {
  _civicrm_recurring_mail_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civicrm_recurring_mail_civicrm_xmlMenu(&$files) {
  _civicrm_recurring_mail_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civicrm_recurring_mail_civicrm_install() {
  _civicrm_recurring_mail_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civicrm_recurring_mail_civicrm_postInstall() {
  _civicrm_recurring_mail_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civicrm_recurring_mail_civicrm_uninstall() {
  _civicrm_recurring_mail_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civicrm_recurring_mail_civicrm_enable() {
  _civicrm_recurring_mail_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civicrm_recurring_mail_civicrm_disable() {
  _civicrm_recurring_mail_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civicrm_recurring_mail_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civicrm_recurring_mail_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civicrm_recurring_mail_civicrm_managed(&$entities) {
  _civicrm_recurring_mail_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civicrm_recurring_mail_civicrm_caseTypes(&$caseTypes) {
  _civicrm_recurring_mail_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civicrm_recurring_mail_civicrm_angularModules(&$angularModules) {
  _civicrm_recurring_mail_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civicrm_recurring_mail_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civicrm_recurring_mail_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function civicrm_recurring_mail_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values){
  if($objectName == 'Mailing' && in_array($op, array('view.mailing.browse.scheduled', 'view.mailing.browse.unscheduled', 'view.mailing.browse'))){
    $recurrence = new CRM_Mailing_Recur_BAO_Recurrence;
    if($recurrence->get('mailing_id', $objectId)){
      foreach($links as $key => $link){
        if(in_array($link['name'], array('Cancel', 'Continue', 'Delete', 'Re-Use'))){
          unset($links[$key]);
        }
      }
      $links[] = [
        'name' => 'Edit master',
        'url' => 'civicrm/mailing/send',
        'qs' => "mid={$recurrence->getMasterMailingId()}&continue=true&reset=1",
        'title' => 'Edit master',
        'bit' => 2
      ];

    }
  }
}

function civicrm_recurring_mail_civicrm_apiWrappers(&$wrappers, $apiRequest){
  if ($apiRequest['entity'] == 'Mailing' && $apiRequest['action'] == 'submit') {
    $wrappers[] = new CRM_Mailing_Recur_Wrapper_MailingSubmit();
  }
}

function civicrm_recurring_mail_civicrm_pre($op, $objectName, $id, &$params){
  // When deleting a mailing, check to see if it is a recurring mailing
  if($objectName == 'Mailing' && $op == 'delete'){
    if(CRM_Mailing_Recur_BAO_MailingRecur::isRecurringMailing($id)){
      //If so, unschedule it before deleting (this will delete the recurrences)
      civicrm_api3('MailingRecur', 'unschedule', ['mailing_id' => $id]);
    }
  }
}

function civicrm_recurring_mail_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name'  => 'MailingRecur',
    'class' => 'CRM_Mailing_Recur_DAO_MailingRecur',
    'table' => 'civicrm_mailing_recur',
  );
  $entityTypes[] = array(
    'name'  => 'MailingRecurrence',
    'class' => 'CRM_Mailing_Recur_DAO_Recurrence',
    'table' => 'civicrm_mailing_recurrence',
  );
}

function civicrm_recurring_mail_civicrm_alterAngular($angular){
  $changeSet = \Civi\Angular\ChangeSet::create('recurring_mail')
    ->alterHtml('~/crmMailing/BlockSchedule.html',
      function (phpQueryObject $doc) {
        $doc->find('.crmMailing-schedule-inner')->append('<crm-mailing-block-schedule-recur-option></crm-mailing-block-schedule-recur-option>');
    });
  $angular->add($changeSet);
}
