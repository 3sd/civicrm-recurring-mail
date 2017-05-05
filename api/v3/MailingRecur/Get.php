<?php

/**
 * MailingRecur.Schedule API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_mailing_recur_Get_spec(&$spec) {
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
function civicrm_api3_mailing_recur_Get($params) {
  // var_dump(_civicrm_api3_get_BAO(__FUNCTION__));
  // exit;
  return _civicrm_api3_basic_get('CRM_Mailing_Recur_BAO_MailingRecur', $params);
  //
  // // Check that mailing_id exists and throw an error if not
  // //
  // $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
  // $mailingRecur->get($params);
  // return civicrm_api3_create_success($mailingRecur, $params, 'MailingRecur', 'Get');
}
