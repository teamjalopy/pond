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
        'method': 'GET',
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
                $scope.user = response.data.data;
                console.log($scope.user);
                $scope.username = $scope.user.name;
                if($scope.username == '' || $scope.username == null){
                    $scope.username = $scope.user.email;
                }
                console.log($scope.username);
                
            },
            function errorCallback(response) {
                console.log('Getting username unsuccessful')
            }
    
    );

    //logout test function
    $scope.logOut = function() {
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    }

}]);