'use strict';

var app =
angular.module('pond', [
    'ngRoute',
    'ngAnimate',
    'ngCookies',
    'vcRecaptcha',
    'pond.HomeView',
    'pond.LoginView',
    'pond.DashboardView'
])
.config(['$routeProvider', function($routeProvider) {
        $routeProvider.otherwise({ redirectTo: '/' });
}])
.value('settings',{
    'baseURI': 'http://pond.dev/'
});

// Prevent view/partial caching
app.run(function($rootScope, $templateCache) {
   $rootScope.$on('$viewContentLoaded', function() {
      $templateCache.removeAll();
   });
});

// Compare To validator
// [CITE] http://plnkr.co/edit/FipgiTUaaymm5Mk6HIfn?p=preview
var compareTo = function() {
    return {
        require: "ngModel",
        scope: {
            otherModelValue: "=compareTo"
        },
        link: function(scope, element, attributes, ngModel) {

            ngModel.$validators.compareTo = function(modelValue) {
                return modelValue == scope.otherModelValue;
            };

            scope.$watch("otherModelValue", function() {
                ngModel.$validate();
            });
        }
    };
}

app.directive("compareTo", compareTo);
