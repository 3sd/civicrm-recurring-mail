(function(angular, $, _) {

  // declare your module
  angular.module('civicrm-recurring-mail', []);

  // Append something to BlockSchedule
    angular.module('crmMailing').config(function($provide){
      // intercept crmMailingBlockMailing directive, note the 'Directive' suffix
      $provide.decorator('crmMailingBlockScheduleDirective', function($delegate){
        var directive = $delegate[0];

        // From here on, it's a WIP...

        // Original compile function
        var compile = directive.compile;
        // New compile function
        directive.compile = function(Element, Attrs){
          var link = compile.apply(this, arguments);
          // Append your custom directive
          // Element('.crmMailing-schedule-inner').append('michael');
          return function(scope, elem, attrs){
            link.apply(this, arguments);
          };
        };
        return $delegate;
    });
  });
})(angular, CRM.$, CRM._);
