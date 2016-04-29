'use strict';

angular.module('pond.SettingsView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/settings', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'SettingsController'
    });
}])

.controller('SettingsController', function($scope, settings, $controller) {

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

    $scope.pagePartial = "/app/SettingsView/SettingsPartial.html";
});
