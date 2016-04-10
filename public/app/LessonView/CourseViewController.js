// Course View JS
'use strict';

angular.module('pond.CourseView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/course-view' , {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'CourseViewController'
    });
}])

.controller('CourseViewController', ['$scope', '$http', '$location', '$cookies', 'settings', function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/LessonView/CourseViewPartial.html';
}]);
