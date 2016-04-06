//Student Dash JS
'use strict';

angular.module('pond.StudentDashView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-dash', {
        templateUrl: 'app/common/DashTemplate.html'
        controller: 'StudentDashController'
    });
}])

.controller('StudentDashController', ['$scope', '$http', '$location', 'settings', function($scope, $http, $location, settings) {
    $scope.pagePartial = "/app/StudentDashView/StudentDashPartial.html";
}]);