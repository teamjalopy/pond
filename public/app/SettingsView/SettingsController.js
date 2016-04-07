'use strict';

angular.module('pond.SettingsView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/settings', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'SettingsController'
    });
}])

.controller('SettingsController', ['$scope', 'settings',
function($scope, settings) {
    $scope.pagePartial = "/app/SettingsView/SettingsPartial.html";
}]);
