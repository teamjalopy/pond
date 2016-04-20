// Module editor Controller JS
'use strict';

angular.module('pond.ModuleEditorView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/modules/:moduleID' , {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'ModuleEditorController'
    });
}])

.controller('ModuleEditorController',
function($scope, $http, $location, $cookies, $routeParams, $controller, settings, $uibModal) {
    $scope.pagePartial = '/app/ModuleEditorView/ModuleEditorViewPartial.html';
    $scope.loadedStudents = false;
    $scope.loadedModules = false;

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);
    
});
