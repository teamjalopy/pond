'use strict';

angular.module('pond.DashboardView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/dashboard', {
        templateUrl: 'app/DashboardView/DashboardView.html',
        controller: 'DashboardController'
    });
}])

.controller('DashboardController', ['$scope', '$http', function($scope, $http) {
    $scope.message = "dashboard controller works!";
}]);
