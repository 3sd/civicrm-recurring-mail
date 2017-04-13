(function(angular, $, _) {

  // declare your module
  angular.module('civicrm-recurring-mail', []);

  angular.module('crmMailing').config(function($provide) {
    // intercept crmMailingBlockMailing directive, note the 'Directive' suffix
    $provide.decorator('crmMailingBlockScheduleDirective', function($delegate) {

      var directive = $delegate[0];

      directive.compile = function(Element, Attrs) {
        // you can manipulate the directive's template here, like adding your own DOM elements/directives
        // console.log(Element);
        Element.children().children().append('<div crm-mailing-block-schedule-recur-option />');
        // return new link function
        return function(scope, elem, attr) {

          // call apply to get original functionality
          directive.link.apply(this, arguments);

          // add new functionality here
          // elem.append('<h1>Text added from new link function</h1>');

        };
      };
      return $delegate;
    });
  });

  // angular.module('crmMailing').controller('crmMailingBlockScheduleRecurOptionCtlr', function($scope) {
  // });

  angular.module('crmMailing').directive('crmMailingBlockScheduleRecurOption', function() {
    return {
      templateUrl: CRM.resourceUrls['civicrm-recurring-mail'] + '/ang/crmMailingBlockScheduleRecurOption.html',
    };
  });

  angular.module('crmMailing').directive('crmMailingRecurSchedule', function() {
    return {
      templateUrl: CRM.resourceUrls['civicrm-recurring-mail'] + '/ang/crmMailingRecurSchedule.html',
      controller: 'crmMailingRecurScheduleCtrl'
    };
  });
  angular.module('crmMailing').controller('crmMailingRecurScheduleCtrl', function($scope, $filter) {

    $scope.intervals = _.range(1, 30);
    $scope.interval = 1;
    $scope.freqs = [
      {
        value: 'DAILY',
        label: 'Daily',
        interval: 'day(s)'
      },
      {
        value: 'WEEKLY',
        label: 'Weekly',
        interval: 'week(s)'
      },
      {
        value: 'MONTHLY',
        label: 'Monthly',
        interval: 'month(s)'
      },
      {
        value: 'YEARLY',
        label: 'Yearly',
        interval: 'year(s)'
      }
    ];
    $scope.freq = $scope.freqs[1];

    $scope.monthRepeats = [
      {
        value: 'dayofweek',
        label: 'Day of the week',
      },
      {
        value: 'dayofmonth',
        label: 'Day of the month',
      },
    ];
    $scope.monthRepeat = $scope.monthRepeats[0];

    $scope.daysOfMonth = _.map(_.range(1, 32), function(x){return {value:x, label:x + nth(x)};});
    $scope.daysOfMonth.push({value:'-1', label:"last"});

    $scope.dayOfMonth = $scope.daysOfMonth[0];

    $scope.weeksOfMonth = _.map(_.range(1, 5), function(x){return {value:x, label:x + nth(x)};});
    $scope.weeksOfMonth.push({value:'-1', label:"last"});

    $scope.weekOfMonth = $scope.weeksOfMonth[0];

    function nth(d) {
      if(d>3 && d<21) return 'th'; // thanks kennebec
      switch (d % 10) {
            case 1:  return "st";
            case 2:  return "nd";
            case 3:  return "rd";
            default: return "th";
        }
    }

    $scope.daysOfWeek = [
      {value: 'MO', label: 'Monday'},
      {value: 'TU', label: 'Tuesday'},
      {value: 'WE', label: 'Wednesday'},
      {value: 'TH', label: 'Thursday'},
      {value: 'FR', label: 'Friday'},
      {value: 'SA', label: 'Saturday'},
      {value: 'SU', label: 'Sunday'}
    ];

    $scope.dayOfWeek = $scope.daysOfWeek[0];

    $scope.setEnd = function (value) {
      $scope.end = value;
    };

    $scope.$watch('until', function() {
      if($scope.until){
        $scope.end = 'until';
      }
    });

    $scope.setWeekly = function(){
      if($scope.freq.value=='WEEKLY'){
        daysString = _.filter($scope.daysOfWeek, function(day){return day.selected;}).map(function(day){ return day.value;}).toString();
        if(daysString.length){
          $scope.weekly = 'BYDAY=' + daysString +';';
          return;
        }
      }
      $scope.weekly = '';
    };

    $scope.$watch('freq', function(){
      $scope.setWeekly();
      $scope.setMonthly();
    });

    $scope.setMonthly = function(){
      if($scope.freq.value=='MONTHLY'){
        if($scope.monthRepeat.value=='dayofweek'){
          console.log('setting monthly...');
          $scope.monthly = 'BYDAY=' + $scope.weekOfMonth.value + $scope.dayOfWeek.value + ';';
          return;
        }else if($scope.monthRepeat.value=='dayofmonth'){
          $scope.monthly = 'BYMONTHDAY=' + $scope.dayOfMonth.value + ';';
          return;
        }
      }
      $scope.monthly = '';
    };


    $scope.$watchGroup(['end', 'until', 'count'], function() {
      switch ($scope.end) {
        case 'until':
          if($scope.until){
            $scope.ends = 'UNTIL=' + $filter('date')($scope.until, "yyyyMMddT235959") + ';';
          }
          break;
        case 'count':
          if($scope.count){
            $scope.ends = 'COUNT=' + $scope.count + ';';
          }
          break;
        default:
          $scope.ends = '';
      }
    });

  });

})(angular, CRM.$, CRM._);
