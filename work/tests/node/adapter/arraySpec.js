'use strict';

var Table = require('../../../../node/table');
var ArrayAdapter = require('../../../../node/adapter/array');

describe("ArrayAdapter", function () {
    var table, adapter, a, b, c, d;

    beforeEach(function () {
        adapter = new ArrayAdapter();

        table = new Table();
        table.setAdapter(adapter);
        table.setColumns({
            id: {
                title: 'ID',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_EQUAL ],
                sortable: true,
                visible: false,
            },
            string: {
                title: 'String',
                type: Table.TYPE_STRING,
                filters: [ Table.FILTER_LIKE, Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            integer: {
                title: 'Integer',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_BETWEEN ],
                sortable: true,
                visible: true,
            },
            float: {
                title: 'Float',
                type: Table.TYPE_FLOAT,
                filters: [ Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            boolean: {
                title: 'Boolean',
                type: Table.TYPE_BOOLEAN,
                filters: [ Table.FILTER_EQUAL, Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            datetime: {
                title: 'DateTime',
                type: Table.TYPE_DATETIME,
                filters: [ Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
        });
        table.setMapper(function (row) {
            var result = row;

            if (row['datetime'] !== null)
                result['datetime'] = row['datetime'].getTime() / 1000;

            return result;
        });

        a = {
            id: 1,
            string: "string 1",
            integer: 1,
            float: 0.01,
            boolean: true,
            datetime: new Date('2010-03-25 13:13:13'),
        };
        b = {
            id: 2,
            string: "string 2",
            integer: 2,
            float: 0.02,
            boolean: false,
            datetime: new Date('2010-03-25 14:14:14'),
        };
        c = {
            id: 3,
            string: null,
            integer: null,
            float: null,
            boolean: null,
            datetime: null,
        };
        d = {
            id: 4,
            string: "string 4",
            integer: 4,
            float: 0.04,
            boolean: false,
            datetime: new Date('2010-03-25 16:16:16'),
        };
    });

    it("check()", function () {
        var thrown = false;
        try {
            adapter.check(table);
        } catch (e) {
            thrown = true;
        }
        expect(thrown).toBeFalsy();
    });

    it("sort()", function () {
        adapter.setData([ a, b, c, d ]);

        ['string', 'integer', 'float', 'datetime'].forEach(function (type) {
            table.setSortColumn(type);
            table.setSortDir(Table.DIR_ASC);

            adapter.sort(table);
            var result = adapter.getData();
            var ids = result.map(function (a) { return a['id']; });
            expect(ids).toEqual([ 3, 1, 2, 4 ]);

            table.setSortColumn(type);
            table.setSortDir(Table.DIR_DESC);

            adapter.sort(table);
            var result = adapter.getData();
            var ids = result.map(function (a) { return a['id']; });
            expect(ids).toEqual([ 4, 2, 1, 3 ]);
        });

        table.setSortColumn('boolean');
        table.setSortDir(Table.DIR_ASC);

        adapter.sort(table);
        var result = adapter.getData();
        var ids = result.map(function (a) { return a['id']; });
        expect(ids).toEqual([ 3, 4, 2, 1 ]);

        table.setSortColumn('boolean');
        table.setSortDir(Table.DIR_DESC);

        adapter.sort(table);
        var result = adapter.getData();
        var ids = result.map(function (a) { return a['id']; });
        expect(ids).toEqual([ 1, 4, 2, 3 ]);
    });

    it("filter()", function () {
        table.setFilters({
            string: {
                like: '2'
            }
        });

        adapter.setData([ a, b, c, d ]);
        adapter.filter(table);
        var result = adapter.getData();

        expect(result.length).toBe(1);
        expect(result[0]['id']).toBe(2);

        table.setFilters({
            boolean: {
                equal: true
            }
        });

        adapter.setData([ a, b, c, d ]);
        adapter.filter(table);
        var result = adapter.getData();

        expect(result.length).toBe(1);
        expect(result[0]['id']).toBe(1);

        table.setFilters({
            integer: {
                between: [1, 3]
            }
        });

        adapter.setData([ a, b, c, d ]);
        adapter.filter(table);
        var result = adapter.getData();

        expect(result.length).toBe(2);
        expect(result[0]['id']).toBe(1);
        expect(result[1]['id']).toBe(2);

        table.setFilters({
            datetime: {
                null: true,
            }
        });

        adapter.setData([ a, b, c, d ]);
        adapter.filter(table);
        var result = adapter.getData();

        expect(result.length).toBe(1);
        expect(result[0]['id']).toBe(3);
    });

    it("paginate()", function (done) {
        var data = [];
        for (var i = 1; i <= 10; i++) {
            data.push({
                id: i,
                string: "string " + i,
                integer: i,
                float: i / 100,
                boolean: (i % 2 == 0),
                datetime: null
            });
        }

        adapter.setData(data);

        table.setPageSize(2);
        table.setPageNumber(2);

        adapter.paginate(table)
            .then(function (data) {
                expect(table.getTotalPages()).toBe(5);
                expect(data.length).toBe(2);
                expect(data[0]['integer']).toBe(3);
                expect(data[1]['integer']).toBe(4);
                done();
            });
    });
});
