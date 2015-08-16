'use strict';

var Table = require('../../../../node/table');
var GenericDbAdapter = require('../../../../node/adapter/genericdb');

describe("PgAdapter", function () {
    var table, adapter, client;

    beforeEach(function () {
        adapter = new GenericDbAdapter();
        adapter.setSelect('*');
        adapter.setFrom('users');
        adapter.setWhere("");
        adapter.setParams([]);

        table = new Table();
        table.setAdapter(adapter);
        table.setColumns({
            id: {
                title: 'ID',
                sql_id: 'id',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_EQUAL ],
                sortable: true,
                visible: false,
            },
            string: {
                title: 'String',
                sql_id: 'string',
                type: Table.TYPE_STRING,
                filters: [ Table.FILTER_LIKE, Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            integer: {
                title: 'Integer',
                sql_id: 'integer',
                type: Table.TYPE_INTEGER,
                filters: [ Table.FILTER_BETWEEN ],
                sortable: true,
                visible: true,
            },
            float: {
                title: 'Float',
                sql_id: 'float',
                type: Table.TYPE_FLOAT,
                filters: [ Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            boolean: {
                title: 'Boolean',
                sql_id: 'boolean',
                type: Table.TYPE_BOOLEAN,
                filters: [ Table.FILTER_EQUAL, Table.FILTER_NULL ],
                sortable: true,
                visible: true,
            },
            datetime: {
                title: 'DateTime',
                sql_id: 'datetime',
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
        table.setSortColumn('boolean');
        table.setSortDir(Table.DIR_ASC);

        adapter.sort(table);
        expect(adapter.sqlOrderBy).toBe('boolean asc');

        table.setSortColumn('boolean');
        table.setSortDir(Table.DIR_DESC);

        adapter.sort(table);
        expect(adapter.sqlOrderBy).toBe('boolean desc');
    });
});
