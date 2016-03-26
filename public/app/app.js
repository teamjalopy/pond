'use strict';

angular.module('pond', [
    'ngRoute',
    'pond.HomeView',
    'pond.LoginView'
])
.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.otherwise({ redirectTo: '/' });
    }
]);
/*
var app = angular.module('pond', ['ngRoute']);

var logInRoute = '/log-in';
var signUpRoute = '/sign-up';

app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.when('/log-in', {
            templateUrl: 'homeTemplate.html',
            controller: 'HomeController'
        })
        .when('/sign-up', {
            templateUrl: 'homeTemplate.html',
            controller: 'SignUpController'
        })
        .otherwise({
            redirectTo: '/sign-up'
        });
    }
]);
*/
