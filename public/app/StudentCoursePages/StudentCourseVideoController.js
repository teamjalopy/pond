'use strict';

angular.module('pond.StudentCourseVideo', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-course-video', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentDashController'
    });
}])

.controller('StudentDashController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentDashView/StudentCourseVideo.html';

}]);