'use strict';

angular.module('pond.StudentCourseTest', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-test', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentCourseTestController'
    });
}])

.controller('StudentCourseTestController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentCoursePages/StudentCourseTest.html';

}]);
