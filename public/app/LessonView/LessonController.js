// Course View JS
'use strict';

angular.module('pond.LessonView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/lesson' , {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'LessonController'
    });
}])

.controller('LessonController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/LessonView/LessonPartial.html';
}]);
