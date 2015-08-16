'use strict';

var Table = require('../../../../node/table');
var PgAdapter = require('../../../../node/adapter/pg');

describe("PgAdapter", function () {
    var table, adapter, client;

    beforeEach(function () {
        client = {
            connect: function (cb) {},
            query: function (query, params, cb) {},
            end: function () {},
        };

        adapter = new PgAdapter();
        adapter.setClient(client);
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

    it("filter()", function () {
        table.setFilters({
            string: {
                like: '2'
            }
        });

        adapter.filter(table);

        expect(adapter.sqlAnds).toEqual({ string: [ 'string ILIKE $1' ] });
        expect(adapter.sqlParams).toEqual([ '%2%' ]);

        table.setFilters({
            boolean: {
                equal: true
            }
        });

        adapter.filter(table);

        expect(adapter.sqlAnds).toEqual({ boolean: [ 'boolean = $1' ] });
        expect(adapter.sqlParams).toEqual([ true ]);

        table.setFilters({
            integer: {
                between: [1, 3]
            }
        });

        adapter.filter(table);

        expect(adapter.sqlAnds).toEqual({ integer: [ 'integer >= $1 AND integer <= $2' ] });
        expect(adapter.sqlParams).toEqual([ 1, 3 ]);

        table.setFilters({
            datetime: {
                null: true,
            }
        });

        adapter.filter(table);

        expect(adapter.sqlAnds).toEqual({ datetime: [ 'datetime IS NULL' ] });
        expect(adapter.sqlParams).toEqual([]);
    });

    it("paginate()", function (done) {
        client.connect = function (cb) {
            cb();
        };

        var queryCounter = 0;
        client.query = function (query, params, cb) {
            query = query.replace(/\s{2,}/g, " ");
            if (++queryCounter == 1) {
                expect(query).toBe("SELECT COUNT(*) AS count FROM users ");
                expect(params).toEqual([]);
                cb(null, { rows: [ { count: 6 } ] });
            } else {
                expect(query).toBe("SELECT * FROM users OFFSET 2 LIMIT 2 ");
                expect(params).toEqual([]);
                cb(null, { rows: [ ] });
            }
        };

        table.setPageSize(2);
        table.setPageNumber(2);

        adapter.paginate(table)
            .then(function (result) {
                expect(table.getTotalPages()).toBe(3);
                done();
            });
    });
});
