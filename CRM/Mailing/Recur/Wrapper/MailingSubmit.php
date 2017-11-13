<?php

class CRM_Mailing_Recur_Wrapper_MailingSubmit{

  public function fromApiInput($apiRequest) {
    if(CRM_Mailing_Recur_BAO_MailingRecur::isRecurringMailing($apiRequest['params']['id'])){
      error_log(__FILE__.':'.__LINE__."\n".print_r($apiRequest['params']['id'], true));
      civicrm_api3('MailingRecur', 'unschedule', ['mailing_id' => $id]);
    }
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

}
