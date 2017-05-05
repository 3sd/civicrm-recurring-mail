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
  $spec['mailing_id']['api.required'] = true;
  $spec['recur']['api.required'] = true;
}

/**
 * MailingRecur.Schedule API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_mailing_recur_Schedule($params) {
  // TODO Check that mailing_id exists and throw an error if not
  $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
  $mailingRecur->schedule($params);
  return civicrm_api3_create_success();
}
