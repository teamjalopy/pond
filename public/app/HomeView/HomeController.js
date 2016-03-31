'use strict';

angular.module('pond.HomeView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'HomeController'
    });
}])

.controller('HomeController', ['$scope', '$http', function($scope, $http) {

    $scope.pagePartial = '/app/HomeView/HomePartial.html';
    $scope.bgClass = 'front';

    $scope.topLinkText = 'Log in';
    $scope.topLink = '#/log-in';

    // Multi-step form animation
    $scope.shiftBy = 350; // amount to increment shift
    $scope.shiftAmount = 0; // counter for shift pixels
    $scope.shiftCSS = ""; // resulting CSS of shift
    $scope.shiftElements = 2;
    $scope.maxShift = $scope.shiftBy * $scope.shiftElements;

    $scope.nextStep = function() { // increment shifter
        $scope.increment($scope.shiftBy * -1);
    };

    $scope.prevStep = function() { // decrement shifter
        $scope.increment($scope.shiftBy);
    };

    $scope.increment = function(amount) {
        $scope.shiftAmount += amount;
        $scope.shiftAmount = $scope.shiftAmount % $scope.maxShift;
        var css = {transform:'translateX('+$scope.shiftAmount+'px)'};
        $scope.shiftCSS = css
    }
}]);
