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

  function getMasterMailingId(){
    $mailingRecur = new CRM_Mailing_Recur_BAO_MailingRecur;
    $mailingRecur->get($this->mailing_recur_id);
    return $mailingRecur->mailing_id;
  }

  function findScheduledRecurringMailings($mailingId){
    $query = "
      SELECT cm_recurrence.id
      FROM civicrm_mailing_recurrence AS recurrence
      JOIN civicrm_mailing_recur AS recur ON recurrence.mailing_recur_id = recur.id
      JOIN civicrm_mailing AS cm_recurrence ON recurrence.mailing_id = cm_recurrence.id
      JOIN civicrm_mailing AS cm_recur ON recur.mailing_id = cm_recur.id
      WHERE (cm_recurrence.is_completed IS NULL OR cm_recurrence.is_completed !=1) AND cm_recur.id = %1
      ORDER BY cm_recurrence.id
      ";
    $scheduledRecurrences = self::executeQuery($query, [1 => [$mailingId, 'Integer']]);
    return $scheduledRecurrences;
  }
}
