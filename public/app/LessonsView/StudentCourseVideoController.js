'use strict';

angular.module('pond.StudentCourseVideo', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-video', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentCourseVideoController'
    });
}])

.controller('StudentCourseVideoController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentDashView/StudentCourseVideo.html';

    $scope.back = function() {
    	$location.path('/student-course-article');
    };
    
    $scope.next = function() {
    	$location.path('/student-course-test');
    };

}]);