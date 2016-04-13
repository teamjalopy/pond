//Student Dash JS
'use strict';

angular.module('pond.StudentDashView', ['ngRoute', 'pond.DashController'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/student-dash', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'StudentDashController'
    });
}])

.controller('StudentDashController',
function($scope, $http, $location, $cookies, settings, $controller) {

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

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
    }).then(
    		function successCallback(response) {
    			//get the user data name
                $scope.user = response.data.data;

                $scope.username = $scope.user.name;
                if($scope.username == '' || $scope.username == null){
                    $scope.username = $scope.user.email;
                }

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

    $scope.lessons = [
        {
            id:   1,
            creator_id:  1,
            lesson_name: "Lesson One",
            published: true,
            created_at: "2016-04-04 19:23:34",
            updated_at: "2016-04-05 14:10:40"
        },
        {
            id:   2,
            creator_id:  1,
            lesson_name: "Lesson Two",
            published: true,
            created_at: "2016-04-04 19:23:34",
            updated_at: "2016-04-05 14:10:40"
        },
        {
            id:   3,
            creator_id:  1,
            lesson_name: "Lesson Three",
            published: true,
            created_at: "2016-04-04 19:23:34",
            updated_at: "2016-04-05 14:10:40"
        }
    ];

    $scope.logOut = function() {
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    }
});
