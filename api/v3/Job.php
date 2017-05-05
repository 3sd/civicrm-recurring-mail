<?php

/**
 * MailingRecur.Schedule API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_job_generate_mailing_recurrences($params){
  $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
  $mailingRecur->find();
  while($mailingRecur->fetch()){
    $mailingRecur->syncRecurrences();
  }
  return civicrm_api3_create_success();
}
