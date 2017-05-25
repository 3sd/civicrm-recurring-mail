(function(angular, $, _) {
  // declare your module
  angular.module('civicrm-recurring-mail', []);

  angular.module('crmMailing').config(function($provide) {
    // intercept crmMailingBlockMailing directive, note the 'Directive' suffix
    $provide.decorator('crmMailingBlockScheduleDirective', function($delegate, crmApi) {



      var directive = $delegate[0];


      directive.compile = function(Element, Attrs) {

        // Add an extra option to the schedule block
        Element.children().children().append('<div crm-mailing-block-schedule-recur-option />');
        // return new link function
        return function(scope, elem, attr) {

          var initialized = false;
          scope.$watch('schedule', function() {
            if(!initialized || 1){
              crmApi('MailingRecur', 'getsingle', {
                mailing_id: scope.mailing.id,
              }).then(function(result) {
                if(!result.is_error){
                  scope.schedule.mode = 'recur';
                  scope.recur = result.recur;
                }
              }).catch(function(err){
gi              });
              initialized = true;
            }


            if (scope.schedule.mode == 'recur') {
              $('.crmMailing-submit-button').hide();
              return;
            }
            $('.crmMailing-submit-button').show();
          }, true);

          // call apply to get original functionality
          directive.link.apply(this, arguments);

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
      controller: 'crmMailingRecurScheduleCtrl',
    };
  });
  angular.module('crmMailing').controller('crmMailingRecurScheduleCtrl', function EditMailingCtrl($scope, $filter, $route, crmApi) {

    // Repeat on a ...

    $scope.freqs = [{
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
    $scope.freq = $scope.freqs[2];



    // Send every...

    $scope.intervals = _.range(1, 30);
    $scope.interval = 1;

    // Starting on...
    // Not currently defined
    $scope.now = new Date();

    // Weekly elements

    $scope.daysOfWeek = [{
        value: 'MO',
        label: 'Monday'
      },
      {
        value: 'TU',
        label: 'Tuesday'
      },
      {
        value: 'WE',
        label: 'Wednesday'
      },
      {
        value: 'TH',
        label: 'Thursday'
      },
      {
        value: 'FR',
        label: 'Friday'
      },
      {
        value: 'SA',
        label: 'Saturday'
      },
      {
        value: 'SU',
        label: 'Sunday'
      }
    ];

    // Note that the selected day of the week is tracked by adding a

    // Monthly elements

    // Monthly: Day of month or day or week

    $scope.monthRepeats = [{
        value: 'dayofweek',
        label: 'Day of the week',
      },
      {
        value: 'dayofmonth',
        label: 'Day of the month',
      },
    ];

    $scope.monthRepeat = $scope.monthRepeats[0];

    // Monthly: Day of month

    $scope.daysOfMonth = _.map(_.range(1, 32), function(x) {
      return {
        value: x,
        label: x + nth(x)
      };
    });

    $scope.daysOfMonth.push({
      value: '-1',
      label: "Last"
    });

    $scope.dayOfMonth = $scope.daysOfMonth[0];

    // Monthly: Day of week (e.g. 3rd Wednesday)

    $scope.weeksOfMonth = _.map(_.range(1, 5), function(x) {
      return {
        value: x,
        label: x + nth(x)
      };
    });

    $scope.weeksOfMonth.push({
      value: '-1',
      label: "Last"
    });
    // e.g. 3rd...
    $scope.weekOfMonth = $scope.weeksOfMonth[0];

    // e.g. ...Wednesday
    $scope.dayOfWeek = $scope.daysOfWeek[0];

    // This function is used to create ordinals for certain numerical form elements (it doesn't exist in lodash)
    function nth(d) {
      if (d > 3 && d < 21) return 'th'; // thanks kennebec
      switch (d % 10) {
        case 1:
          return "st";
        case 2:
          return "nd";
        case 3:
          return "rd";
        default:
          return "th";
      }
    }

    $scope.end = 'never';

    // Used to change end radio form element when an appropriate child element
    // is selected
    $scope.setEnd = function(value) {
      $scope.end = value;
    };

    // Initialize the recur form if this is an existing recurring mailing
    var recurInitialized = false;
    $scope.$watch('recur', function() {
      if(!recurInitialized){
        _.each($scope.recur.split(';'), function(value){
          var parts = value.split('=');
          switch (parts[0]){
            case 'FREQ':
              $scope.freq = _.find($scope.freqs, function(value){ return value.value == parts[1]; });
              break;
            case 'DTSTART':
              $scope.start = new Date(
                parts[1].substr(0,4) + '-' +
                parts[1].substr(4,2) + '-' +
                parts[1].substr(6,2) + 'T' +
                parts[1].substr(9,2) + ':' +
                parts[1].substr(11,2)
              );
              break;
            case 'COUNT':
              $scope.end = 'count';
              $scope.count = parseInt(parts[1]);
              break;
            case 'UNTIL':
              $scope.end = 'until';
              $scout.until = $scope.start = new Date(
                parts[1].substr(0,4) + '-' +
                parts[1].substr(4,2) + '-' +
                parts[1].substr(6,2) + 'T' +
                parts[1].substr(9,2) + ':' +
                parts[1].substr(11,2)
              );
              break;
            case 'BYDAY':
            // At this point, we can be sure that $scope.freq is set correctly,
            // so we can use it to determine what the do with BY
              if($scope.freq.value == 'WEEKLY'){
                _.each(parts[1].split(','), function(selectedDay){
                  _.each($scope.daysOfWeek, function(day){
                    if(day.value==selectedDay){
                      day.selected = true;
                    }
                  });
                });
              }
              if($scope.freq.value == 'MONTHLY'){
                $scope.dayOfWeek = _.find($scope.daysOfWeek, function(value){ return value.value == parts[1].substring(parts[1].length - 2); });
                $scope.weekOfMonth = _.find($scope.weeksOfMonth, function(value){ return value.value == parts[1].substring(0, parts[1].length - 2); });
              }
              break;
          }
        });
        recurInitialized = true;
      }
    });

    $scope.$watch('until', function() {
      if ($scope.until) {
        $scope.end = 'until';
      }
    });

    $scope.setWeekly = function() {

      if ($scope.freq.value == 'WEEKLY') {
        daysString = _.filter($scope.daysOfWeek, function(day) {
          return day.selected;
        }).map(function(day) {
          return day.value;
        }).toString();
        if (daysString.length) {
          $scope.weekly = 'BYDAY=' + daysString + ';';
          return;
        }
      }
      $scope.weekly = '';
    };

    $scope.$watch('freq', function() {
      $scope.setWeekly();
      $scope.setMonthly();
    });

    $scope.$watch('monthRepeat', function() {
      $scope.setMonthly();
    });

    $scope.setMonthly = function() {
      if ($scope.freq.value == 'MONTHLY') {
        if ($scope.monthRepeat.value == 'dayofweek') {
          $scope.monthly = 'BYDAY=' + $scope.weekOfMonth.value + $scope.dayOfWeek.value + ';';
          return;
        } else if ($scope.monthRepeat.value == 'dayofmonth') {
          $scope.monthly = 'BYMONTHDAY=' + $scope.dayOfMonth.value + ';';
          return;
        }
      }
      $scope.monthly = '';
    };

    // There is likely a better way of implementing this validation
    $scope.validateDaysOfWeek = function(){
      if ($scope.freq.value == 'WEEKLY') {
        var selectedDays = daysString = _.filter($scope.daysOfWeek, function(day) {
          return day.selected;
        });
        if(selectedDays.length > 0){
          $scope.crmMailingRecur.$setValidity('atLeastOneDay', true);
        }else{
          $scope.crmMailingRecur.$setValidity('atLeastOneDay', false);
        }
      }else{
        $scope.crmMailingRecur.$setValidity('atLeastOneDay', true);
      }
    };

    $scope.schedule = function() {

      // This validation is hacky, but I am not sure what a better / the correct
      // approach is here.
      $scope.validateDaysOfWeek();

      if($scope.crmMailingRecur.$valid === true){
        $scope.recur = "DTSTART=" + $filter('date')($scope.start, 'yyyyMMddTHHmmss') + ';FREQ=' + $scope.freq.value + ';INTERVAL=' + $scope.interval + ';' + $scope.weekly + $scope.monthly + $scope.ends;
        console.log($scope.recur);
        console.log($scope.mailing.id);
        crmApi('MailingRecur', 'schedule', {
          mailing_id: $scope.mailing.id,
          recur: $scope.recur
        }).then(function(result) {
          window.location = CRM.url('civicrm/mailing/browse/scheduled', { reset: 1, scheduled: 'true' });
        }).catch(function(err){
          console.log(err);
        });
      }else{
        console.log('invalid!');
      }
    };



    $scope.$watchGroup(['end', 'until', 'count'], function() {
      switch ($scope.end) {
        case 'until':
          if ($scope.until) {
            $scope.ends = 'UNTIL=' + $filter('date')($scope.until, "yyyyMMddT235959") + ';';
          }
          break;
        case 'count':
          if ($scope.count) {
            $scope.ends = 'COUNT=' + $scope.count + ';';
          }
          break;
        default:
          $scope.ends = '';
      }
    });

  });

})(angular, CRM.$, CRM._);
