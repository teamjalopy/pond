'use strict';

angular.module('myApp.TeacherView', ['$scope','settings'])

.config(['$routeProvider'], function($routeProvider) {
    $routeProvider.when('/second-view', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'TeacherController'
    });
}])

.controller('TeacherDashController', ['$scope', 'settings'],
function($scope, settings) {
    $scope.viewName = 'TeacherDashView';
    $scope.pagePartial = "/app/TeacherDashView/TeacherDashPartial.html";
}]);
