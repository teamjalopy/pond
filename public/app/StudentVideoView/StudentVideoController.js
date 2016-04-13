'use strict';

angular.module('pond.StudentVideoView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-video', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentVideoController'
    });
}])

.controller('StudentCourseVideoController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentVideoView/StudentVideoPartial.html';

    $scope.back = function() {
    	$location.path('/student-course-article');
    };
    
    $scope.next = function() {
    	$location.path('/student-course-test');
    };

}]);