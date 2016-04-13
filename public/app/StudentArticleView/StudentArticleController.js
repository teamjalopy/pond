//Student Course Article JS
'use strict';

angular.module('pond.StudentArticleView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-article', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentArticleController'
    });
}])


.controller('StudentArticleController', ['$scope', '$http', '$location', '$cookies', 'settings',
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentArticleView/StudentArticlePartial.html';


    $scope.back = function() {
    	$location.path('/student-course-overview');
    };
    
    $scope.next = function() {
    	$location.path('/student-course-video');
    };


}]);