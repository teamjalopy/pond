
var app = angular.module('pond', ['ngRoute']);

app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.when('/log-in', {
            templateUrl: 'log-in.html',
            controller: 'LogInController'
        })
        .when('/sign-up', {
            templateUrl: 'sign-up.html',
            controller: 'SignUpController'
        })
        .otherwise({
            redirectTo: '/sign-up'
        });
    }
]);

app.factory('LandingPage', function() {
    var topLinkText = 'Default';
    var topLinkAddress = '/';
    var bodyClass = 'asdf';
    return {
        // topLinkText: function() { return topLinkText; }
    };
});

app.controller('MainController', function($scope, LandingPage) {
    $scope.LandingPage = LandingPage;
});

app.controller('LogInController', function($scope, LandingPage, $http) {
    LandingPage.topLinkText    = 'Sign up';
    LandingPage.topLinkAddress = '#/sign-up';
    LandingPage.bodyClass      = 'log-in-page';

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
});

app.controller('SignUpController', function($scope, LandingPage) {
    LandingPage.topLinkText    = 'Log in';
    LandingPage.topLinkAddress = '#/log-in';
    LandingPage.bodyClass      = 'sign-up-page';
});
