//Student Dash JS
'use strict';

angular.module('pond.StudentDashView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-dash', {
        templateUrl: 'app/common/DashTemplate.html'
        controller: 'StudentDashController'
        student: ''
    });
}])

.controller('StudentDashController', ['$scope', '$http', '$location', 'settings', 
	function($scope, $http, $location, settings) {
    $scope.pagePartial = "/app/StudentDashView/StudentDashPartial.html";

    $http({
        'method': 'POST',
        'url': settings.baseURI + 'api/users/1',
        'headers': { 'Content-Type' : 'application/json'}, // explicitly provide the content type
        // pass the data object (the Content-Type above will mean it gets implicitly encoded as JSON)
        'data': name
    })
    .then( function(response){
    	$scope.student = reponse.data.name
    }
    );

}]);