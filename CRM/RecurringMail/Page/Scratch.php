<?php

class CRM_RecurringMail_Page_Scratch extends CRM_Core_Page
{
  public function run()
  {
    // foreach(civicrm_api3('mailing', 'get', ['option.limit' => 10000])['values'] as $m){
    //   civicrm_api3('mailing', 'delete', ['id' => $m['id']]);
    // }
    // exit;

    $recur = new CRM_RecurringMail_BAO_MailingRecur;
    $recur->id=2;
    if(!$recur->find()){
      throw new Exception('Could not find recurring mailing');
    }
    $recur->fetch();
    $recur->syncRecurrences();

    $recurrences = new CRM_RecurringMail_BAO_Recurrence;
    $recurrences->mailing_recur_id=6;
    $recurrences->find();
    while($recurrences->fetch()){
      var_dump($recurrences->getMailing()['scheduled_date']);
    }
  }
}
