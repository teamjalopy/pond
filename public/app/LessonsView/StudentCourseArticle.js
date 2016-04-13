'use strict';

angular.module('pond.StudentCourseArticle', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-article', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentCourseArticleController'
    });
}])

.controller('StudentCourseArticleController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentDashView/StudentCourseArticle.html';


    $scope.back = function() {
    	$location.path('/student-course-overview');
    };
    
    $scope.next = function() {
    	$location.path('/student-course-video');
    };


}]);