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
function($scope, $http, $location, $cookies, $routeParams, $controller, settings, $uibModal) {
    $scope.pagePartial = '/app/LessonView/LessonPartial.html';

    // Inherit DashController
    $controller('DashController', {$scope: $scope});
    console.log($scope.baseController);

    $scope.$watch('dashPage',function(){
        $scope.backPage = $scope.dashPage;
        console.log($scope.backPage);
    });

    // Load lessons
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
            $scope.loadStudents();
            $scope.loadModules();
        },
        function errorCallback(response) {
            console.error('Failed to load lesson');
            console.log(response);
        }
    );

    // Load modules
    $scope.loadModules = function() {
        $http({
            'method': 'GET',
            'url': settings.baseURI + 'api/lessons/' + $routeParams.lessonID + '/modules',
            'headers': {
            	'Content-Type' : 'application/json',
            	'Authorization' : 'Bearer ' + $cookies.get('token')
            }
        }).then(
            function successCallback(response) {
                console.log("Got the modules for this lesson");
                console.log(response.data);
                $scope.modules = response.data.data;
            },
            function errorCallback(response) {
                console.error('Failed to load modules');
                console.log(response);
            }
        );
    };

    // Load students
    $scope.loadStudents = function() {
        $http({
            'method': 'GET',
            'url': settings.baseURI + 'api/lessons/' + $scope.lesson.id + '/students',
            'headers': {
                'Content-Type' : 'application/json',
                'Authorization' : 'Bearer ' + $cookies.get('token')
            }
        })
        .then(
            function successCallback(response) {
                console.log('Got the students for this lesson');
                $scope.students = response.data.data;
            },
            function errorCallback(response) {
                console.error('Could not load the students for this lesson.');
                console.error(response);
            }
        );
    };

    $scope.showStudents = function(lesson,students) {
        var modal = $uibModal.open({
            animation: true,
            templateUrl: 'studentsModal.html',
            controller: 'studentsModalController',
            size: null,
            resolve : {
                lesson: function() {
                    return lesson;
                },
                students: function() {
                    return students;
                }
            }
        });
    }; // showStudents

})

// Modal for adding students to lessons
// Template: studentsModal.html
.controller('studentsModalController',
function($scope, $uibModalInstance, $http, $cookies, settings, lesson, students) {

    $scope.lesson = lesson;
    $scope.students = students;

    $scope.cancel = function() { $uibModalInstance.close(); };

    $scope.addNewStudents = function() {
        var newStudentsData = {
            'emails' : $scope.newStudentEmails
        };

        $http({
            'method': 'POST',
            'url': settings.baseURI + 'api/lessons/' + $scope.lesson.id + '/students',
            'headers': {
                'Content-Type' : 'application/json',
                'Authorization' : 'Bearer ' + $cookies.get('token')
            },
            'data': newStudentsData
        })
        .then(
            function successCallback(response) {
                console.log('Successful adding of new students');
            },
            function errorCallback(response) {
                console.error('Could not add new students.');
            }
        );
    };

});
