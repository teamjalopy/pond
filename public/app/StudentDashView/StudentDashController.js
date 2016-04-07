//Student Dash JS
'use strict';

angular.module('pond.StudentDashView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-dash', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentDashController'
    });
}])

.controller('StudentDashController', ['$scope', '$http', '$location', '$cookies', 'settings', 
function($scope, $http, $location, $cookies, settings) {
    $scope.pagePartial = '/app/StudentDashView/StudentDashPartial.html';

    $scope.username = '';

    $http({
        'method': 'POST',
        'url': settings.baseURI + 'api/users/me',
        'headers': { 
        	'Content-Type' : 'application/json',
        	'Authorization' : 'Bearer ' + $cookies.get('token')
        }, // explicitly provide the content type
        // pass the data object (the Content-Type above will mean it gets implicitly encoded as JSON)
    })
    .then( 
    		function successCallback(response) {
    			//get the user data name
                $scope.username = reponse.data.data;
    			console.log($scope.user);
            },
            function errorCallback(response) {
                //$scope.submitEnabled = true;
                $scope.errors.push({
                    'message': response.data.message
                });
            }
    
    );

}]);