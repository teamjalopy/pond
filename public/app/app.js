'use strict';

var app =
angular.module('pond', [
    'ngRoute',
    'ngAnimate',
    'ngCookies',
    'vcRecaptcha',
    'ui.bootstrap',
    'ngTagsInput',
    'pond.HomeView',
    'pond.LoginView',
    'pond.DashController',
    'pond.StudentDashView',
    'pond.TeacherDashView',
    'pond.SettingsView',
    'pond.StudentModuleView',
    'pond.LessonView',
    'pond.ModuleEditorView'
])
.config(['$routeProvider', function($routeProvider) {
        $routeProvider.otherwise({ redirectTo: '/' });
}])
.value('settings',{
    'baseURI': 'https://pond.dev/'
});

// Prevent view/partial caching
// [CITE] https://gist.github.com/claudemamo/9092047#file-app-2-js
app.run(function($rootScope, $templateCache) {
    $rootScope.$on('$routeChangeStart', function(event, next, current) {
        if (typeof(current) !== 'undefined'){
            $templateCache.remove(current.templateUrl);
        }
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
