'use strict';

var dtModule = angular.module('dynamicTable', []);

dtModule.provider('dynamicTable', function () {
    var translationFilter = null;
    var defaultOptions = {
        row_id_column: null,
        mapper: null,
        sort_column: null,
        sort_dir: 'asc',
        page_number: 1,
        page_size: 15,
        page_sizes: [ 15, 30, 50, 100, 0 ],
        table_class: 'table table-striped table-hover table-condensed',
        loader_image: 'img/loader.gif',
        strings: {
            DT_BANNER_LOADING: 'Loading... Please wait',
            DT_BANNER_EMPTY: 'Nothing found',
            DT_BUTTON_PAGE_SIZE: 'Page size',
            DT_BUTTON_COLUMNS: 'Columns',
            DT_BUTTON_REFRESH: 'Refresh',
            DT_BUTTON_OK: 'OK',
            DT_BUTTON_CLEAR: 'Clear',
            DT_BUTTON_CANCEL: 'Cancel',
            DT_TITLE_FILTER_WINDOW: 'Filter',
            DT_LABEL_CURRENT_PAGE: 'Current page',
            DT_LABEL_ALL_PAGES: 'All pages',
            DT_LABEL_PAGE_OF_1: 'Page',
            DT_LABEL_PAGE_OF_2: 'of #',
            DT_LABEL_FILTER_LIKE: 'Strings like',
            DT_LABEL_FILTER_EQUAL: 'Values equal to',
            DT_LABEL_FILTER_BETWEEN_START: 'Values greater than or equal to',
            DT_LABEL_FILTER_BETWEEN_END: 'Values less than or equal to',
            DT_LABEL_FILTER_NULL: 'Include rows with empty value in this column',
            DT_LABEL_TRUE: 'True',
            DT_LABEL_FALSE: 'False',
            DT_DATE_TIME_FORMAT: 'YYYY-MM-DD HH:mm:ss',
        },
    };

    this.$get = [ '$filter', '$rootScope', function ($filter, $rootScope) {
        if (translationFilter) {
            var strings = defaultOptions.strings;
            $.each(strings, function (key, value) {
                defaultOptions.strings[key] = $filter(translationFilter)(key);
            });
        }

        var Service = function (options) {
            this.element = null,
            this.plugin = null,
            this.event = null,
            this.options = options;

            this.init = function (element) {
                if (this.plugin)
                    return;

                var mergedOptions = defaultOptions;
                $.extend(mergedOptions, options);
                this.options = mergedOptions;

                this.element = $(element);
                this.plugin = this.element.dynamicTable(this.options);

                var service = this;
                this.element.on('dt.loading', function (e) {
                    service.event = 'loading';
                    $rootScope.$digest();
                });
                this.element.on('dt.loaded', function (e) {
                    service.event = 'loaded';
                    $rootScope.$digest();
                });
                this.element.on('dt.selected', function (e) {
                    service.event = 'selected';
                    $rootScope.$digest();
                });
                this.element.on('dt.deselected', function (e) {
                    service.event = 'deselected';
                    $rootScope.$digest();
                });
            };
        };
 
        return function (options) {
            return new Service(options);
        };
    } ];

    this.setTranslationFilter = function (filter) {
        translationFilter = filter;
    };
});

dtModule.directive('dynamicTable',
    [ function() {
        return {
            restrict: 'A',
            scope: {
                ctrl: '=dynamicTable',
            },
            link: function(scope, element, attrs) {
                if (typeof attrs['id'] == 'undefined')
                    console.log('DynamicTable expects id attribute to be set');

                if (angular.isDefined(scope.ctrl) && angular.isDefined(scope.ctrl.init))
                    scope.ctrl.init(element);
            }
        };
    } ]
);
