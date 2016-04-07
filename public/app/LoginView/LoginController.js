'use strict';

angular.module('pond.LoginView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/log-in', {
        templateUrl: 'app/common/LandingTemplate.html',
        controller: 'LoginController'
    });
}])

.controller('LoginController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {

    $scope.pagePartial = '/app/LoginView/LoginPartial.html';
    $scope.bgClass = 'login';

    $scope.topLinkText = 'Sign up';
    $scope.topLink = '#/';

    $scope.errors = [];
    $scope.submitEnabled = true;

    $scope.needLogin = $location.search().e == 'needLogin';
    $scope.didLogOut = $location.search().e == 'didLogOut';

    $scope.submitLogin = function() {

        $scope.errors = [];
        $scope.submitEnabled = false;

        $location.search('e', null);

        // Must use `this` instead of `$scope` for model access because login form
        // is in an ngInclude rather than directly in the template (LandingTemplate),
        // so $scope is lost because Angular creates a child scope. `this` will always resolve
        // to the current scope (the child scope in this case, because submitLogin()
        // is therein triggered).

        var loginData = { 'email' : this.loginEmail, 'password' : this.loginPassword };

        $http({
            'method': 'POST',
            'url': settings.baseURI + 'api/auth',
            'headers': { 'Content-Type' : 'application/json'},
            'data': loginData
        })
        .then(
            function successCallback(response) {
                // Store token
                console.log(response.data.data.token);
                $cookies.put('token', response.data.data.token);
                // Redirect to dashboard
                if(!response.data.data.is_teacher){
                    $location.path('/student-dash');
                }
                else{
                    $location.path('/teacher-dash');
                }
                
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
