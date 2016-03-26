'use strict';

angular.module('pond.HomeView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'HomeController'
    });
}])

.controller('HomeController', ['$scope', '$http', function($scope, $http) {
    console.log('home controller');

    $scope.pagePartial = '/app/HomeView/HomePartial.html';
    $scope.bgClass = 'front';

    $scope.topLinkText = 'Log in';
    $scope.topLink = '#/log-in';
}]);
