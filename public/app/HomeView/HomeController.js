'use strict';

angular.module('pond.HomeView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'HomeController'
    });
}])

.controller('HomeController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {

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
    }

    $scope.prevStep = function() { // decrement shifter
        $scope.increment($scope.shiftBy);
    }

    $scope.increment = function(amount) {
        $scope.shiftAmount += amount;
        $scope.shiftAmount = $scope.shiftAmount % $scope.maxShift;
        var css = {transform:'translateX('+$scope.shiftAmount+'px)'};
        $scope.shiftCSS = css
    }

    $scope.errors = [];
    $scope.submitEnabled = true;
    $scope.registered = false;

    $scope.submitRegistration = function() {

            if($scope.registered) {
                console.log('already registered.');
                return;
            }

            $scope.errors = [];
            $scope.submitEnabled = false;

            // $location.search('e', null);

            var registrationData = {
                'email' : this.signupEmail,
                'password' : this.signupPassword,
                'captcha' : this.signupCaptcha
            };

            console.log(registrationData);

            $http({
                'method': 'POST',
                'url': settings.baseURI + 'api/users',
                'headers': { 'Content-Type' : 'application/json'},
                'data': registrationData
            })
            .then(
                function successCallback(response) {
                    console.log("Register success");
                    $scope.registered = true;
                },
                function errorCallback(response) {
                    $scope.submitEnabled = true;
                    $scope.errors.push({
                        'message': response.data.message
                    });
                    console.log(response.data);
                }
            );
    }
}]);
