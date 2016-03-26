'use strict';

angular.module('pond', [
    'ngRoute',
    'ngAnimate',
    'ngCookies',
    'pond.HomeView',
    'pond.LoginView',
    'pond.DashboardView'
])
.config(['$routeProvider', function($routeProvider) {
        $routeProvider.otherwise({ redirectTo: '/' });
}])
.value('settings',{
    'baseURI': 'http://pond.dev/'
});
