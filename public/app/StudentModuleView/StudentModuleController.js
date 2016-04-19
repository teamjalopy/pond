//Student Dash JS
'use strict';

angular.module('pond.StudentModuleView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/module', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentModuleController'
    });
}])

.controller('StudentModuleController',
function($scope, $http, $location, $cookies, settings, $controller) {
	$scope.pagePartial = "app/StudentModuleView/StudentQuizPartial.html";
});