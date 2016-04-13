'use strict';

angular.module('pond.DashController', [])
.controller('DashController', function($scope){
    $scope.baseController = "DashController";
    $scope.navCollapsed = true;

    $scope.$on('$routeChangeStart', function(event, next, current) {
        if (typeof(current) !== 'undefined'){
            console.log("Route change. Collapsing nav.")
            // Forcing $scope to update
            // [CITE] http://nathanleclaire.com/blog/2014/01/31/banging-your-head-against-an-angularjs-issue-try-this/
            $scope.apply(function(){
                $scope.navCollapsed = true;
            });
        }
    });
});
