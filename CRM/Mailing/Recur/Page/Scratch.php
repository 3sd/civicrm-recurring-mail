<?php

class CRM_Mailing_Recur_Page_Scratch extends CRM_Core_Page
{
  public function run()
  {
    $this->deleteAllMailings();
    // $this->outputRecurInstances();
  }

  public function deleteAllMailings(){
    foreach(civicrm_api3('mailing', 'get', ['option.limit' => 10000])['values'] as $m){
      civicrm_api3('mailing', 'delete', ['id' => $m['id']]);
    }
  }

  public function outputRecurInstances(){
    $rule = \Recurr\Rule::createFromString('DTSTART=20170401T000000;FREQ=DAILY;INTERVAL=1;UNTIL=20170430T235959;');
    $transformer = new \Recurr\Transformer\ArrayTransformer();
    $count = 0;
    foreach($transformer->transform($rule) as $date){
      $recurrences[] = $date->getStart();
      $count++;
      if($count == 50){
        break;
      }
    }
    var_dump($recurrences);
  }
}
