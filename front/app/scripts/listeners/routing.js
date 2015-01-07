'use strict';

angular.module('appApp')
    .run(function($rootScope, $state, $error) {
        $rootScope.$on('$stateChangeError',
            function(event, toState, toParams, fromState, fromParams, error) {
                $error.queue.push({
                    message: toState.data && toState.data.resolveError ? toState.data.resolveError(toParams) : undefined
                });
                $state.go('error', {code: error.status});
            }
        );
    })
;
