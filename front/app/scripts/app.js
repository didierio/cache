'use strict';

angular
    .module('appApp', [
        'ngAnimate',
        'ngCookies',
        'ngResource',
        'ngSanitize',
        'ui.router',
        'ngTouch'
    ])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider, $locationProvider) {
        $stateProvider
            .state('layout', {
                abstract: true,
                views: {
                    '': {
                        templateUrl: 'views/layout.html'
                    },
                    'header@layout': {
                        controller: function () {
                        }
                    }
                }
            })
        ;

        $urlRouterProvider.otherwise('/error/404');

        $locationProvider.html5Mode(true);
    })
;
