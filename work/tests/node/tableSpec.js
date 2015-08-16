'use strict';

var q = require('q');
var Table = require('../../../node/table');

describe("Table", function () {
    var table;

    beforeEach(function () {
        table = new Table();
    });

    it("setColumns() accepts valid data", function () {
        var thrown = false;
        try {
            table.setColumns({
                id: {
                    title: 'ID',
                    type: Table.TYPE_INTEGER,
                    filters: [ Table.FILTER_LIKE ],
                    sortable: true,
                    visible: true,
                }
            });
        } catch (e) {
            thrown = true;
        }

        expect(thrown).toBeFalsy();
    });

    it("setFilters() corrects params", function () {
        table.setColumns({
            id: {
                title: 'ID',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_EQUAL ],
                sortable: true,
                visible: true,
            }
        });

        table.setFilters({
            id: {
                equal: 123,
                like: 'xxx',
            },
            missing: {
                equal: 123,
            }
        });

        var expected = {
            id: {
                equal: 123,
            },
        };

        expect(table.getFilters()).toEqual(expected);
    });

    it("setSortColumn() corrects params", function () {
        table.setColumns({
            id: {
                title: 'ID',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_EQUAL ],
                sortable: true,
                visible: true,
            }
        });

        table.setSortColumn('id');
        expect(table.getSortColumn()).toBe('id');

        table.setSortColumn('missing');
        expect(table.getSortColumn()).toBeNull();
    });

    it("calculates page params", function () {
        table.setPageNumber(999);
        table.setPageSize(12);

        table.calculatePageParams(134);
        var total = table.getTotalPages();
        var number = table.getPageNumber();

        expect(total).toBe(Math.ceil(134 / 12));
        expect(number).toBe(total);
    });

    it("describes", function (done) {
       var columns = {
            id: {
                title: 'ID',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_EQUAL ],
                sortable: true,
                visible: true,
            }
        };

        table.setColumns(columns);
        table.describe(function (err, result) {
            expect(typeof result['columns']).toBe('object');
            expect(result['columns']).toEqual(columns);
            done();
        });
    });

    it("fetches", function (done) {
        var adapter = {
            check: function () { return null; },
            filter: function () { return null; },
            sort: function () { return null; },
            paginate: function () {
                var defer = q.defer();
                defer.resolve([ 'foobar' ]);
                return defer.promise;
            },
        };

        table.setAdapter(adapter);
        table.fetch(function (err, data) {
            expect(typeof data).toBe('object');
            expect(typeof data['sort_column']).not.toBe('undefined');
            expect(typeof data['sort_dir']).not.toBe('undefined');
            expect(typeof data['page_number']).not.toBe('undefined');
            expect(typeof data['page_size']).not.toBe('undefined');
            expect(typeof data['total_pages']).not.toBe('undefined');
            expect(typeof data['filters']).not.toBe('undefined');
            expect(typeof data['rows']).not.toBe('undefined');
            done();
        });
    });
});
