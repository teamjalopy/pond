'use strict';

angular.module('pond.LoginView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/log-in', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'LoginController'
    });
}])

.controller('LoginController', ['$scope', '$http', 'settings', function($scope, $http, settings) {

    $scope.pagePartial = '/app/LoginView/LoginPartial.html';
    $scope.bgClass = 'login';

    $scope.topLinkText = 'Sign up';
    $scope.topLink = '#/';

    $scope.errors = [];
    $scope.submitEnabled = true;

    $scope.submitLogin = function() {

        $scope.errors = [];
        $scope.submitEnabled = false;

        // Must use `this` instead of `$scope` for model access because login form
        // is in an ngInclude rather than directly in the template (LandingTemplate),
        // so $scope is lost because Angular creates a child scope. `this` will always resolve
        // to the current scope (the child scope in this case, because submitLogin()
        // is therein triggered).

        var loginData = { 'username' : this.loginUsername, 'password' : this.loginPassword };

        $http({
            'method': 'POST',
            'url': settings.baseURI + 'api/auth',
            'headers': { 'Content-Type' : 'application/json'},
            'data': loginData
        })
        .then(
            function successCallback(response) {
                console.log(response);
                console.log("TODO redirect and store token");
            },
            function errorCallback(response) {
                $scope.submitEnabled = true;
                $scope.errors.push({
                    'message': response.data.message
                });
            }
        );
    };

}]);
