'use strict';

angular.module('appApp')
    .factory('$error', function () {
        return {
            queue: []
        };
    })
;
