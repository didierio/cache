'use strict';

angular
    .module('appApp')

    .config(function($stateProvider) {
        $stateProvider.state('error', {
            parent: 'layout',
            url: '/error/{code}',
            views: {
                content: {
                    controller: 'ErrorCtrl',
                    templateUrl: 'views/error.html'
                }
            }
        });
    })

    .controller('ErrorCtrl', function ($scope, $stateParams, $error) {
        $scope.code = $stateParams.code;

        var error = $error.queue.pop();
        $scope.message = error && error.message || 'Undefined error';
    })
;
