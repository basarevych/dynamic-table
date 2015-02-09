'use strict';

describe('dynamicTable', function () {

    var element, scope, dynamicTable = null,
        backendUrl = '/foobar.php', calledUrl = null, filterCalled = null;

    function compileDirective(tpl) {
        inject(function ($compile) {
            element = $compile(tpl)(scope);
            element.appendTo(document.body);
        });
        scope.$digest();
    }

    beforeEach(function (){
        angular.mock.module('dynamicTable', function (dynamicTableProvider, $provide) {
            dynamicTableProvider.setTranslationFilter('foobar');
            $provide.value('$filter', function (filterName) {
                return function (key) {
                    filterCalled = filterName;
                };
            });
        });
    });

    beforeEach(inject(function (_dynamicTable_) {
        dynamicTable = _dynamicTable_;
        jQuery.getJSON = function (url, params, success) {
            calledUrl = url;
            success({});
        };
    }));

    beforeEach(inject(function ($rootScope) {
        scope = $rootScope.$new();
    }));


    it('loads translations', function () {
        expect(filterCalled).toBe('foobar');
    });

    it('creates correct instance', function () {
        var inst = dynamicTable({ url: backendUrl });
        expect(typeof inst).toBe('object');
        expect(inst.options['url']).toBe(backendUrl);
    });

    it('underlying plugin works', function () {
        scope.ctrl = dynamicTable({ url: backendUrl });
        compileDirective('<div id="foo" dynamic-table="ctrl"><div>');

        expect(calledUrl).toBe(backendUrl);

        scope.ctrl.element.trigger('dt.loading');
        expect(scope.ctrl.event).toBe('loading');

        scope.ctrl.element.trigger('dt.loaded');
        expect(scope.ctrl.event).toBe('loaded');

        scope.ctrl.element.trigger('dt.selected');
        expect(scope.ctrl.event).toBe('selected');

        scope.ctrl.element.trigger('dt.deselected');
        expect(scope.ctrl.event).toBe('deselected');
    });

});
