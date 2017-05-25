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
    // Get the master mailing.
    $masterMailing = civicrm_api3('mailing', 'getsingle', ['id' => $this->mailing_id]);

    // Get the list of dates that we would expect to exist, based on the rule.
    $expectedDates = $this->getExpectedDates();

    //TODO If there are no expected dates, mark this recurring mail as completed
    // (which means it will be ignored when running the cron to generate new
    // recurrences
    // if(!count($expectedDates)){
    // }

    // To start, assume that none are in existence...
    $existingDates = [];

    // ... and that we need to create them all.
    $datesToCreate = $expectedDates;

    // Cycle through the list of existing recurrences.
    $existingRecurrences = new CRM_Mailing_Recur_BAO_Recurrence;
    $existingRecurrences->mailing_recur_id = $this->id;
    $existingRecurrences->find();
    while($existingRecurrences->fetch()){

      // Retreive the mailing linked to this recurrence.
      $mailing = $existingRecurrences->getMailing();

      // Check if the scheduled date of this mailing is in our range of expected
      // dates.

      $expectedKey = array_search(new DateTime($mailing['scheduled_date']), $expectedDates);

      if($expectedKey !== false){
        // If it is, we don't need to create it.
        unset($datesToCreate[$expectedKey]);
      }else{
        // If it isn't we should delete it.
        civicrm_api3('mailing', 'delete', ['id' => $existingRecurrences->mailing_id]);
      }

      // Now check to see if we already have a recurrence set at this time.
      $existingKey = array_search(new DateTime($mailing['scheduled_date']), $existingDates);

      if($existingKey !== false){
        // If we do, then consider this recurrence as a duplicate and delete it.
        civicrm_api3('mailing', 'delete', ['id' => $existingRecurrences->mailing_id]);
      }else{
        // If we don't then add this time to the list of existing dates to check
        // against next time around.
        $existingDates[] = new DateTime($mailing['scheduled_date']);
      }
    }

    // We have now deleted all duplicate and out of range recurrences.

    // We need to create all missing recurrences.
    foreach($datesToCreate as $date){

      // Base the parameters for the recurrence on the master mailing
      $mailingParams = $masterMailing;
      unset($mailingParams['id']);
      $mailingParams['name'] = $mailingParams['name'].' (recurring)';
      // Create and submit a new mailing with the appropriate date
      $createdMailing = civicrm_api3('mailing', 'create', $mailingParams);
      $submittedMailing = civicrm_api3('mailing', 'submit', [
        'id' => $createdMailing['id'],
        'scheduled_date' => $date->format('YmdHis')
      ]);

      // Create a recurrence for this mailing
      CRM_Mailing_Recur_BAO_Recurrence::create([
        'mailing_id' => $createdMailing['id'],
        'mailing_recur_id' => $this->id
      ]);
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
