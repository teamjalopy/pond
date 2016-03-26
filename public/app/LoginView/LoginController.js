'use strict';

angular.module('pond.LoginView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/log-in', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'LoginController'
    });
}])

.controller('LoginController', ['$scope', '$http', function($scope, $http) {
    console.log('login controller');

    $scope.pagePartial = '/app/LoginView/LoginPartial.html';
    $scope.bgClass = 'login';

    $scope.topLinkText = 'Sign up';
    $scope.topLink = '#/';

    $scope.submitLogin = function() {
        var loginData = { 'username' : $scope.loginUsername, 'password' : $scope.loginPassword };
        console.log(loginData);

        $http({
            'method': 'POST',
            'url': 'http://private-ff358f-pond1.apiary-mock.com/auth',
            'headers': { 'Content-Type' : 'application/json'},
            'data': loginData
        })
        .then(
            function successCallback(response) {
                console.log(response);
            },
            function errorCallback(response) {
                console.log("error callback");
            }
        );
    };

}]);
