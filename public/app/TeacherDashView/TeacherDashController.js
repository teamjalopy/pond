'use strict';

angular.module('pond.TeacherDashView', ['ngRoute', 'pond.DashController'])

.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/teacher-dash', {
        templateUrl: 'app/common/DashTemplate.html',
        controller: 'TeacherDashController'
    });
}])

.controller('TeacherDashController',
function($scope, settings, $location, $cookies, $http, $uibModal, $controller) {

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

    $scope.pagePartial = "/app/TeacherDashView/TeacherDashPartial.html";

    $scope.username = '';

    $scope.lessons = [];

    $scope.showNewLessonForm = false;

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
        console.log("Delete lesson: "+lesson.name);
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

        modal.result.then(function(success) {
            if(success) {
                console.log("Removing lesson from list...");
                var index = $scope.lessons.indexOf(lesson);
                $scope.lessons.splice(index, 1);
            } else {
                console.log("Did not receive success signal from Delete action");
            }
        });
    }

    $scope.saveNewLesson = function() {
        var data = {'name': NewLessonForm.NewLessonName.value };
        console.log(data);
        $http({
            'method': 'POST',
            'url': settings.baseURI + 'api/lessons',
            'headers': {
                'Content-Type' : 'application/json',
                'Authorization' : 'Bearer ' + $cookies.get('token')
            },
            'data': data
        })
        .then(
            function successCallback(response) {
                console.log('New Lesson Success');
                $scope.lessons.push(response.data.data);
                $scope.showNewLessonForm = false;
            },
            function errorCallback(response) {
                console.error('Lesson Edit form save action failed.');
            }
        );
    }
})

// Modal for editing lessons
// Template: editLessonModal.html
.controller('editLessonModalController',
function($scope, $uibModalInstance, lesson, $http, settings, $cookies) {

    $scope.lesson = angular.copy(lesson);

    $scope.save = function() {

        var editLessonData = {
            'name' : $scope.lesson.name,
            'published' : $scope.lesson.published
        };

        $http({
            'method': 'PUT',
            'url': settings.baseURI + 'api/lessons/' + $scope.lesson.id,
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
.controller('deleteLessonModalController',
function($scope, $uibModalInstance, lesson, $http, settings, $cookies) {

    $scope.lesson = lesson;
    console.log("Lesson for deletion:");
    console.log($scope.lesson);

    $scope.confirm = function() {
        $http({
            'method': 'DELETE',
            'url': settings.baseURI + 'api/lessons/' + $scope.lesson.id,
            'headers': {
                'Content-Type' : 'application/json',
                'Authorization' : 'Bearer ' + $cookies.get('token')
            }
        })
        .then(
            function successCallback(response) {
                console.log('Delete Success');
                $uibModalInstance.close(true);
            },
            function errorCallback(response) {
                console.error('Lesson Delete form action failed.');
                console.error(response);
                $uibModalInstance.close(false);
            }
        );
    };
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
});
