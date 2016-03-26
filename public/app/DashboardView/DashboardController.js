'use strict';

angular.module('pond.DashboardView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/dashboard', {
        templateUrl: 'app/DashboardView/DashboardView.html',
        controller: 'DashboardController'
    });
}])

.controller('DashboardController', ['$scope', '$http', '$cookies', '$location',
function($scope, $http, $cookies, $location) {

    $scope.$on('$routeChangeSuccess', function () {
        if(!$cookies.get('token')) {
            $location.search('e','needLogin');
            $location.path('/log-in');
        }
    });

    $scope.message = "Dashboard controller works!";

    $scope.logOut = function() {
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    }
}]);
