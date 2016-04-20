// Module editor Controller JS
'use strict';

angular.module('pond.ModuleEditorView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/modules/1' , {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'ModuleEditorController'
    });
}])

.controller('ModuleEditorController',
function($scope, $http, $location, $cookies, $routeParams, $controller, settings, $uibModal) {
    $scope.pagePartial = '/app/ModuleEditorView/ModuleEditorPartial.html';
    $scope.loadedStudents = false;
    $scope.loadedModules = false;

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

     $scope.choices = [{id: 'choice1'}, {id: 'choice2'}];
  
  $scope.addNewChoice = function() {
    var newItemNo = $scope.choices.length+1;
    $scope.choices.push({'id':'choice'+newItemNo});
  };
    
  $scope.removeChoice = function() {
    var lastItem = $scope.choices.length-1;
    $scope.choices.splice(lastItem);
  };
  

    
});
