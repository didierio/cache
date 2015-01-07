'use strict';

angular.module('appApp')
    .config(function ($stateProvider) {
        $stateProvider
            .state('index', {
                url: '',
                controller: function($state) {
                    $state.go('home');
                }
            })
            .state('home', {
                parent: 'layout',
                url: '/',
                views: {
                    content: {
                        controller: 'MainCtrl',
                        templateUrl: 'views/main.html'
                    }
                }
            })
        ;
    })

    .controller('MainCtrl', function () {
    })
;
