<?php

/**
 * MailingRecur.Schedule API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_mailing_recur_Schedule_spec(&$spec) {
}

/**
 * MailingRecur.Unschedule API
 *
 * Deletes all recurrences and deletes the mailing recur. Leaves the mailing
 * itself. In other words, converts a recurring mailing to a normal mailing.
 * This function is called when deleting a mailing, and also when submitting
 * a mailing, since submission (which I am fairly sure if the right thing to).
 */
function civicrm_api3_mailing_recur_Unschedule($params) {
  // Check that mailing_id exists and throw an error if not
  $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
  foreach($params as $key => $value){
    $mailingRecur->$key = $value;
  }
  $mailingRecur->find();
  while($mailingRecur->fetch()){
    $mailingRecur->deleteRecurrenceMailings();
    $mailingRecur->delete();
  }
  return civicrm_api3_create_success();
}
