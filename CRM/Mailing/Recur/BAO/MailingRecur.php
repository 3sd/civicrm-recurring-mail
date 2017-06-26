<?php

class CRM_Mailing_Recur_BAO_MailingRecur extends CRM_Mailing_Recur_DAO_MailingRecur {

  // The maxium amount of recurrences that should be created. 25 instances
  // won't clutter the UI too much and are fairly fast to create. As long as we
  // run the CRON job more frequently than the time period between the first and
  // last recurrence, we won't miss any recurrences.
  const MAX_RECURRENCES = 25;

  /**
   * Create a new RecurRule based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Mailing_Recur_DAO_RecurRule|NULL
   **/
  public static function create($params) {
    $entityName = 'RecurRule';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self;
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  function schedule($params){
    if($this->get('mailing_id', $params['mailing_id'])){
      $this->recur = $params['recur'];
      $this->update();
    }else{
      $this->recur = $params['recur'];
      $this->mailing_id = $params['mailing_id'];
      $this->insert();
    }
    $this->syncRecurrences();
  }

  function syncRecurrences(){

    // Strategy:
    // * Cycle through existing mailings
    // * Update each mailing based on:
    //    * the master mailing
    //    * the master mailing groups
    //    * an expected date
    // If we run out of dates, remove any remaining existing mailings
    // If dates remain, create new mailings

    // TODO If there are no expected dates, mark this recurring mail as
    // completed, which means it will be ignored when running the cron to
    // generate new recurrences.

    // Get the master mailing and create params from it
    $this->masterMailing = civicrm_api3('Mailing', 'getsingle', ['id' => $this->mailing_id]);

    // Get the master mailing groups
    $this->masterMailingGroups = civicrm_api3('MailingGroup', 'get', ['mailing_id' => $this->mailing_id])['values'];

    // Get a list of existing recurrences
    $scheduledRecurrences = CRM_Mailing_Recur_BAO_Recurrence::findScheduledRecurringMailings($this->mailing_id);

    // var_dump($scheduledRecurrences);
    // Get an array of dates that we would expect to exist, based on the rule.
    $expectedDates = $this->getExpectedDates();

    // Cycle through existing mailings
    while($scheduledRecurrences->fetch()){

      // Take the next date parameter (if one exists)
      if($date = array_shift($expectedDates)){

        // Set the scheduled date param
        $params = ['scheduled_date' => $date->format('Y-m-d H:i:s')];
        // Sync the mailing to the master
        $this->syncMailing($params, $scheduledRecurrences->id);

        // Sync the mailing groups the master
        $this->syncMailingGroups($scheduledRecurrences->id);
      }else{
        civicrm_api3('Mailing', 'delete', ['id' => $scheduledRecurrences->id]);
        // echo 'no more date params';
      }
    };

    // Any values still present in expected dates need to be created
    foreach($expectedDates as $date){

      // Set the scheduled date param
      $params = ['scheduled_date' => $date->format('Y-m-d H:i:s')];

      // Sync the mailing to the master
      $mailingId = $this->syncMailing($params);

      // Sync the mailing groups the master
      $this->syncMailingGroups($mailingId);
    }
    return;
  }

  function syncMailing($params, $mailingId = null){
    $params = $this->masterMailing + $params;
    if($mailingId){
      $params['id'] = $mailingId;
    }else{
      unset($params['id']);
    }
    $result = civicrm_api3('Mailing', 'create', $params);
    if(!$mailingId){
      CRM_Mailing_Recur_BAO_Recurrence::create([
        'mailing_id' => $result['id'],
        'mailing_recur_id' => $this->id
      ]);
    }
    return $result['id'];
  }

  // TODO This function is fairly expensive. It would not be too hard to
  // refactor and have most of the comparisons happening in php rather than via
  // queries
  function syncMailingGroups($mailingId){
    $existingMailingGroups = civicrm_api3('MailingGroup', 'get', ['mailing_id' => $mailingId])['values'];

    foreach($this->masterMailingGroups as $params){
      unset($params['id']);
      $params['mailing_id'] = $mailingId;
      $result = civicrm_api3('MailingGroup', 'get', $params);
      if($result['count']){
        unset($existingMailingGroups[$result['id']]);
      }else{
        $result = civicrm_api3('MailingGroup', 'create', $params);
      }
    }
    foreach($existingMailingGroups as $group){
      civicrm_api3('MailingGroup', 'delete', ['id' => $group['id']]);
    }
  }

  function getExpectedDates(){
    $rule = \Recurr\Rule::createFromString($this->recur);
    $transformer = new \Recurr\Transformer\ArrayTransformer();
    $count = 0;
    foreach($transformer->transform($rule) as $date){

      // Only add the recurrence if the date is in the future
      if($date->getStart() > new DateTime){
        $recurrences[] = $date->getStart();
        $count++;
      }

      // Finish once we have created enough recurrences
      if($count == self::MAX_RECURRENCES){
        break;
      }
    }
    return $recurrences;
  }

  function deleteRecurrenceMailings(){
    $recurrences = new CRM_Mailing_Recur_BAO_Recurrence;
    $recurrences->mailing_recur_id = $this->id;
    $recurrences->find();
    while($recurrences->fetch()){
      civicrm_api3('Mailing', 'delete', ['id' => $recurrences->mailing_id]);
    }
  }

  function isRecurringMailing($mailing_id){
    return (boolean) $this->get('mailing_id', $mailing_id);
  }
}
