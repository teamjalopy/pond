'use strict';

angular.module('pond.TeacherDashView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/teacher-dash', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'TeacherDashController'
    });
}])

.controller('TeacherDashController', ['$scope', 'settings',
function($scope, settings) {
    $scope.pagePartial = "/app/TeacherDashView/TeacherDashPartial.html";
}]);
