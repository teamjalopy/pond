'use strict';

angular.module('pond.StudentTestView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-test', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentTestController'
    });
}])

.controller('StudentTestController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentTestView/StudentTestPartial.html';

    $scope.back = function() {
    	$location.path('/student-course-video');
    };
    
    //this might be disabled
    $scope.next = function() {
    	$location.path('/student-course-cert');
    };

}]);
