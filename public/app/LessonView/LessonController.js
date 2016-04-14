// Course View JS
'use strict';

angular.module('pond.LessonView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/lessons/:lessonID' , {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'LessonController'
    });
}])

.controller('LessonController',
function($scope, $http, $location, $cookies, $routeParams, $controller, settings) {
    $scope.pagePartial = '/app/LessonView/LessonPartial.html';

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

    $scope.$watch('dashPage',function(){
        $scope.backPage = $scope.dashPage;
        console.log($scope.backPage);
    });

    $http({
        'method': 'GET',
        'url': settings.baseURI + 'api/lessons/' + $routeParams.lessonID,
        'headers': {
        	'Content-Type' : 'application/json',
        	'Authorization' : 'Bearer ' + $cookies.get('token')
        }
    }).then(
        function successCallback(response) {
            console.log(response.data);
            $scope.lesson = response.data.data;
        },
        function errorCallback(response) {
            console.error('Failed to load lesson');
            console.log(response);
        }
    );
});
