'use strict';

var dtModule = angular.module('dynamicTable', []);

dtModule.provider('dynamicTable', function () {
    var translationFilter = null;
 
    this.$get = [ function () {
        var Service = function (options) {
            this.options = options;
        };

        Service.prototype = {
            init: function (element) {
                this.element = $(element);
                this.plugin = element.dynamicTable(this.options);
            },
        };
 
        return function (options) {
            return new Service(options);
        };
    } ];

    this.setTranslationFilter = function (filter) {
        translationFilter = filter;
    };
});
