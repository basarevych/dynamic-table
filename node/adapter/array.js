/**
 * Array data adapter
 */

'use strict';

var q = require('q');
var moment = require('moment-timezone');
var Table = require('../table');
var BaseAdapter = require('./base');

function ArrayAdapter() {
    /**
     * The data
     */
    this.data = [];
}

ArrayAdapter.prototype = new BaseAdapter();
ArrayAdapter.prototype.constructor = ArrayAdapter;

/**
 * Data setter
 *
 * @param {array} data
 * @return {object}
 */
ArrayAdapter.prototype.setData = function (data) {
    this.data = data;
    return this;
};

/**
 * Data getter
 *
 * @return {array}
 */
ArrayAdapter.prototype.getData = function () {
    return this.data;
};

/**
 * Check table and data
 *
 * @param {object} table
 * @throws Throws when error found
 */
ArrayAdapter.prototype.check = function (table) {
    var columns = table.getColumns();
    var filters = table.getFilters();
    var successfulFilters = {};
    for (var id in filters) {
        if (!filters.hasOwnProperty(id))
            continue;

        var filterData = filters[id];
        var successfulNames = {};
        for (var name in filterData) {
            if (!filterData.hasOwnProperty(name))
                continue;

            var value = filterData[name];
            var test = checkFilter(name, columns[id]['type'], value, null);
            if (test !== null)
                successfulNames[name] = value;
        }
        if (Object.keys(successfulNames).length > 0)
            successfulFilters[id] = successfulNames;
    }
    table.setFilters(successfulFilters);
};

/**
 * Filter data
 *
 * @param {object} table
 */
ArrayAdapter.prototype.filter = function (table) {
    var filters = table.getFilters();
    if (Object.keys(filters).length == 0)
        return;

    var columns = table.getColumns();
    var result = [];
    var me = this;
    this.data.forEach(function (row) {
        var passedAnds = true;
        for (var id in filters) {
            if (!filters.hasOwnProperty(id))
                continue;

            var filterData = filters[id];
            var passedOrs = false;
            for (var name in filterData) {
                if (!filterData.hasOwnProperty(name))
                    continue;

                var value = filterData[name];

                var real = row[id];
                if (columns[id]['type'] == Table.TYPE_DATETIME && real) {
                    var m;
                    if (me.getDbTimezone()) {
                        if (typeof real == 'number')
                            m = moment.unix(real);
                        else
                            m = moment.tz(real, me.getDbTimezone());
                    } else {
                        if (typeof real == 'number')
                            m = moment.unix(real);
                        else
                            m = moment(real);
                    }
                    real = m.local();
                }
            
                var test = checkFilter(name, columns[id]['type'], value, real);
                if (test === true)
                    passedOrs = true;
            }
            if (!passedOrs)
                passedAnds = false;
        }
        if (passedAnds)
            result.push(row);
    });

    this.data = result;
};

/**
 * Sort data
 *
 * @param {object} table
 */
ArrayAdapter.prototype.sort = function (table) {
    var columns = table.getColumns();
    var column = table.getSortColumn();
    var dir = table.getSortDir();

    if (!column)
        return;

    var type = columns[column]['type'];
    var cmp = function (a, b) {
        a = a[column];
        b = b[column];

        if (a === null && b !== null)
            return dir == Table.DIR_ASC ? -1 : 1;
        if (a !== null && b === null)
            return dir == Table.DIR_ASC ? 1 : -1;
        if (a === null && b === null)
            return 0; 

        switch (type) {
            case Table.TYPE_BOOLEAN:
                a = a ? 1 : 0;
                b = b ? 1 : 0;
            case Table.TYPE_INTEGER:
            case Table.TYPE_FLOAT:
            case Table.TYPE_DATETIME:
                if (a == b)
                    return 0;
                if (dir == Table.DIR_ASC)
                    return (a < b) ? -1 : 1;
                return (a < b) ? 1 : -1;
            case Table.TYPE_STRING:
                if (dir == Table.DIR_ASC)
                    return a.localeCompare(b);
                return b.localeCompare(a);
            default:
                throw new Error("Unknown field type: " + type);
        }
    };

    this.data.sort(cmp);
};

/**
 * Paginate and return result
 *
 * @param {object} table
 * @return {object} Returns promise
 */
ArrayAdapter.prototype.paginate = function (table) {
    table.calculatePageParams(this.data.length);

    var data;
    if (table.getPageSize() > 0) {
        var offset = table.getPageSize() * (table.getPageNumber() - 1);
        var length = table.getPageSize();
        data = this.data.slice(offset, offset + length);
    } else {
        data = this.data;
    }

    var mapper = table.getMapper();

    var result = [];
    var me = this;
    data.forEach(function (row) {
        var columns = table.getColumns();
        for (var columnId in columns) {
            var column = columns[columnId];
            if (column.type == Table.TYPE_DATETIME && row[columnId]) {
                var m;
                if (me.getDbTimezone()) {
                    if (typeof row[columnId] == 'number')
                        m = moment.unix(row[columnId]);
                    else
                        m = moment.tz(row[columnId], me.getDbTimezone());
                } else {
                    if (typeof row[columnId] == 'number')
                        m = moment.unix(row[columnId]);
                    else
                        m = moment(row[columnId]);
                }
                row[columnId] = m.local();
            }
        }

        result.push(mapper ? mapper(row) : row);
    });

    var defer = q.defer();
    defer.resolve(result);
    return defer.promise;
};

/**
 * Check and apply filter
 *
 * @param {string} filter
 * @param {string} type
 * @param {*} test
 * @param {*} real
 */
function checkFilter(filter, type, test, real) {
    if (type == Table.TYPE_DATETIME) {
        if (typeof real != 'object')
            real = new Date(real * 1000);
        if (filter == Table.FILTER_BETWEEN
                && Array.isArray(test) && test.length == 2) {
            test = [
                test[0] ? moment.unix(test[0]) : null,
                test[1] ? moment.unix(test[1]) : null,
            ];
        } else if (filter != Table.FILTER_BETWEEN) {
            test = moment.unix(test);
        } else {
            return null;
        }
    } else {
        if (filter == Table.FILTER_BETWEEN) {
            if (!Array.isArray(test) || test.length != 2)
                return null;
        }
    }

    switch (filter) {
        case Table.FILTER_LIKE:
            return real !== null && real.indexOf(test) != -1;
        case Table.FILTER_EQUAL:
            return real !== null && test == real;
        case Table.FILTER_BETWEEN:
            if (real === null)
                return false;
            if (test[0] !== null && real < test[0])
                return false;
            if (test[1] !== null && real > test[1])
                return false;
            return true;
        case Table.FILTER_NULL:
            return real === null;
        default:
            throw new Error("Unknown filter: " + filter);
    }

    return false;
}

module.exports = ArrayAdapter;
