'use strict';

angular.module('pond.DashController', [])
.controller('DashController', function($scope,$cookies,$location){
    $scope.baseController = "DashController";
    $scope.navCollapsed = true;

    $scope.logOut = function() {
        console.log("Logging out...");
        $cookies.remove('token');
        $location.search('e','didLogOut');
        $location.path('/log-in');
    }
});
