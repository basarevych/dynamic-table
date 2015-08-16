/**
 * PG data adapter
 */

'use strict';

var q = require('q');
var Table = require('../table');
var GenericDbAdapter = require('./genericdb');

function PgAdapter() {
    /**
     * PG client
     */
    this.pgClient = null;

    GenericDbAdapter.call(this);
}

PgAdapter.prototype = new GenericDbAdapter();
PgAdapter.prototype.constructor = PgAdapter;

/**
 * PG client setter
 *
 * @param {object} client
 * @return {object}
 */
PgAdapter.prototype.setClient = function (client) {
    this.pgClient = client;
    return this;
};

/**
 * PG client getter
 *
 * @return {object}
 */
PgAdapter.prototype.getClient = function () {
    return this.pgClient;
};

/**
 * Paginate and return result
 *
 * @param {object} table
 * @return {object} Returns promise
 */
PgAdapter.prototype.paginate = function (table) {
    var ands = [];
    for (var filter in this.sqlAnds) {
        var ors = this.sqlAnds[filter];
        ands.push('(' + ors.join(') OR (') + ')');
    }

    var where = '';
    if (this.initialWhere.length > 0) {
        where = ' WHERE (' + this.initialWhere + ')';
        if (ands.length)
            where += ' AND (' + ands.join(') AND (') + ')';
    } else if (ands.length) {
        where = ' WHERE (' + ands.join(') AND (') + ')';
    }

    var me = this;
    var db = this.getClient();
    var mapper = table.getMapper();
    if (!mapper)
        throw new Error("Data 'mapper' is required when using PgAdapter");

    var defer = q.defer();
    db.connect(function (err) {
        if (err)
            throw err;

        db.query(
            "SELECT COUNT(*) AS count"
          + "  FROM " + me.initialFrom + " "
          + where + " ",
            me.sqlParams,
            function (err, result) {
                if (err) {
                    db.end();
                    defer.reject(err);
                    return;
                }

                var count = result.rows[0].count;
                table.calculatePageParams(count);

                if (me.sqlOrderBy.length)
                    where += ' ORDER BY ' + me.sqlOrderBy + ' ';
                if (table.getPageSize() > 0) {
                    where += ' OFFSET ' + (table.getPageSize() * (table.getPageNumber() - 1)) + ' ';
                    where += ' LIMIT ' + table.getPageSize() + ' ';
                }

                db.query(
                    "SELECT " + me.initialSelect + " "
                  + "  FROM " + me.initialFrom + " "
                  + where + " ",
                    me.sqlParams,
                    function (err, result) {
                        if (err) {
                            db.end();
                            defer.reject(err);
                            return;
                        }

                        db.end();

                        var rows = [];
                        for (var i = 0; i < result.rows.length; i++)
                            rows.push(mapper(result.rows[i]));

                        defer.resolve(rows);
                    }
                );
            }
        );
    });

    return defer.promise;
};

/**
 * Build SQL query for a filter
 *
 * @param {string} field
 * @param {string} type
 * @param {string} filter
 * @param {string} value
 * @return {boolean} True on success
 */
PgAdapter.prototype.buildFilter = function (field, type, filter, value) {
    if (field.length == 0)
        throw new Error("Empty 'field'");
    if (type.length == 0)
        throw new Error("Empty 'type'");

    if (type == Table.TYPE_DATETIME) {
        if (filter == Table.FILTER_BETWEEN
                && Array.isArray(value) && value.length == 2) {
            value = [
                value[0] ? new Date(value[0] * 1000) : null,
                value[1] ? new Date(value[1] * 1000) : null,
            ];
        } else if (filter != Table.FILTER_BETWEEN
                && !Array.isArray(value)) {
            value = new Date(value * 1000);
        } else {
            return false;
        }
    } else {
        if (filter == Table.FILTER_BETWEEN) {
            if (!Array.isArray(value) || value.length != 2)
                return false;
        } else if (Array.isArray(value)) {
            return false;
        }
    }

    var counter = this.sqlParams.length + 1;
    if (typeof this.sqlAnds[field] == 'undefined')
        this.sqlAnds[field] = [];

    switch (filter) {
        case Table.FILTER_LIKE:
            this.sqlAnds[field].push(field + " ILIKE $" + counter++);
            this.sqlParams.push('%' + value + '%');
            break;
        case Table.FILTER_EQUAL:
            this.sqlAnds[field].push(field + " = $" + counter++);
            this.sqlParams.push(value);
            break;
        case Table.FILTER_BETWEEN:
            var ands = [];
            if (value[0] !== null) {
                ands.push(field + " >= $" + counter++);
                this.sqlParams.push(value[0]);
            }
            if (value[1] !== null) {
                ands.push(field + " <= $" + counter++);
                this.sqlParams.push(value[1]);
            }
            this.sqlAnds[field].push(ands.join(' AND '));
            break;
        case Table.FILTER_NULL:
            this.sqlAnds[field].push(field + " IS NULL");
            break;
        default:
            throw new Error("Unknown filter: " + filter);
    }

    return true;
};

module.exports = PgAdapter;
