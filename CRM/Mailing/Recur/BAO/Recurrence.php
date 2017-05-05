<?php

  class CRM_Mailing_Recur_BAO_Recurrence extends CRM_Mailing_Recur_DAO_Recurrence {

  /**
   * Create a new Recurrence based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Mailing_Recur_DAO_Recurrence|NULL
   **/
  public static function create($params) {
    $className = 'CRM_Mailing_Recur_DAO_Recurrence';
    $entityName = 'Recurrence';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  function getMailing(){
    return civicrm_api3('mailing', 'getsingle', ['id' => $this->mailing_id]);
  }

  function getMasterMailingId(){
    $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
    $mailingRecur->get($this->mailing_recur_id);
    return $mailingRecur->mailing_id;
  }
}
