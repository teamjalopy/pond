'use strict';

angular.module('pond.TeacherDashView', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/teacher-dash', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'TeacherDashController'
    });
}])

.controller('TeacherDashController', ['$scope', 'settings', '$location', '$cookies', '$http', '$uibModal',
function($scope, settings, $location, $cookies, $http, $uibModal) {
    $scope.pagePartial = "/app/TeacherDashView/TeacherDashPartial.html";

    $scope.username = '';

    $scope.lessons = [];

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

    $http({
        'method': 'GET',
        'url': settings.baseURI + 'api/users/me/lessons',
        'headers': {
        	'Content-Type' : 'application/json',
        	'Authorization' : 'Bearer ' + $cookies.get('token')
        }
    }).then(
        function successCallback(response) {
            console.log(response.data);
            $scope.lessons = response.data.data;
        },
        function errorCallback(response) {
            console.error('Failed to load lessons');
            console.log(response);
        }
    );

    $scope.logOut = function() {
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    }

    $scope.editLesson = function(lesson) {
        var modal = $uibModal.open({
            animation: true,
            templateUrl: 'editLessonModal.html',
            controller: 'editLessonModalController',
            size: null,
            resolve : {
                lesson: function() {
                    return lesson;
                }
            }
        });

        modal.result.then(function(lesson) {
            // nothing
        });
    }

    $scope.deleteLesson = function(lesson) {
        console.log("Delete lesson: "+lesson.lesson_name);
        var modal = $uibModal.open({
            animation: true,
            templateUrl: 'deleteLessonModal.html',
            controller: 'deleteLessonModalController',
            size: null,
            resolve : {
                lesson: function() {
                    return lesson;
                }
            }
        });
    }
}])

// Modal for editing lessons
// Template: editLessonModal.html
.controller('editLessonModalController', function($scope, $uibModalInstance, lesson, $http, settings, $cookies) {

    $scope.lesson = angular.copy(lesson);

    $scope.save = function() {

        var editLessonData = {
            'lesson_name' : $scope.lesson.lesson_name,
            'published' : $scope.lesson.published
        };

        $http({
            'method': 'PUT',
            'url': settings.baseURI + 'api/lessons/' + $scope.lesson.lesson_id,
            'headers': {
                'Content-Type' : 'application/json',
                'Authorization' : 'Bearer ' + $cookies.get('token')
            },
            'data': editLessonData
        })
        .then(
            function successCallback(response) {
                console.log('Success');
                angular.copy(response.data.data, lesson);
                $uibModalInstance.close();
            },
            function errorCallback(response) {
                console.error('Lesson Edit form save action failed.');
            }
        );
    };

    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
})


// Modal for deleting lessons
// Template: deleteLessonModal.html
.controller('deleteLessonModalController', function($scope, $uibModalInstance, lesson) {

    $scope.result = {};

    $scope.lesson = lesson;
    console.log($scope.lesson);

    $scope.confirm = function() {
        console.log("Dummy delete confirm button");
        $uibModalInstance.close($scope.result);
    };
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
});
