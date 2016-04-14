'use strict';

angular.module('pond.DashController', [])
.controller('DashController', function($scope,$cookies,$location,$http,settings){
    $scope.baseController = "DashController";
    $scope.navCollapsed = true;

    $scope.user = null;

    $scope.backPage = null;
    $scope.dashPage = null;

    $scope.logOut = function() {
        console.log("Logging out...");
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    };

    $http({
        'method': 'GET',
        'url': settings.baseURI + 'api/users/me',
        'headers': {
        	'Content-Type' : 'application/json',
        	'Authorization' : 'Bearer ' + $cookies.get('token')
        } // explicitly provide the content type
        // pass the data object (the Content-Type above will mean it gets implicitly encoded as JSON)
    }).then(
		function successCallback(response) {
			//get the user data name
            $scope.user = response.data.data;

            $scope.username = $scope.user.name;
            if($scope.username == '' || $scope.username == null){
                $scope.username = $scope.user.email;
            }

            $scope.dashPage = (function(){
                if($scope.user.is_teacher) { return '#/teacher-dash'; }
                else { return '#/student-dash'; }
            })();

            $scope.user.type = (function(){
                if($scope.user.is_teacher) {
                    return 'Teacher';
                }
                else if($scope.user.is_student) {
                    return 'Student';
                }
                else {
                    console.error("Unknown user type!");
                }
            })();
        },
        function errorCallback(response) {
            console.log('Getting username unsuccessful')
        }
    );
});
