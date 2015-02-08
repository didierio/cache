'use strict';

angular
    .module('appApp', [
        'ngAnimate',
        'ngCookies',
        'ngResource',
        'ngMessages',
        'mgcrea.ngStrap',
        'ngSanitize',
        'ui.router',
        'ngTouch',
        'ngMessages',
        'satellizer'
    ])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider, $locationProvider, $authProvider) {
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

 $authProvider.google({
      clientId: '1068026553468-j6lc330i90mif48pq3kfnukunc2aj4r3.apps.googleusercontent.com'
    });

        $authProvider.oauth2({
            name: 'connect',
            url: '/auth/connect',
            redirectUri: window.location.origin,
            clientId: '52kqb40nk0w0s4cokkws4gc0ck4ggw8cgcgsg0gc8cooc8c4oo',
            authorizationEndpoint: 'http://connect.didier.io/oauth/v2/auth',
        });

        $urlRouterProvider.otherwise('/error/404');

//        $locationProvider.html5Mode(true);
    })
;
