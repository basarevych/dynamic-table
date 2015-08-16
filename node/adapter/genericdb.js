/**
 * Base class for DB data adapter
 */

'use strict';

var clone = require('clone');
var Table = require('../table');
var BaseAdapter = require('./base');

function GenericDbAdapter() {
    /**
     * Initial SELECT clause
     */
    this.initialSelect = "*";

    /**
     * Initial FROM clause
     */
    this.initialFrom = "";

    /**
     * Initial WHERE clause
     */
    this.initialWhere = "";

    /**
     * Initial bound parameters
     */
    this.initialParams = [];

    /**
     * SQL ANDs (will go to WHERE)
     *
     * Each item is an array of ORs, example:
     * {
     *   id: [
     *     'id > $1',   // or 'id > ?'
     *     'id IS NULL',
     *   ],
     * }
     */
    this.sqlAnds = {};

    /**
     * Parameters of SQL where
     */
    this.sqlParams = [];

    /**
     * SQL order by clause
     */
    this.sqlOrderBy = "";

    BaseAdapter.call(this);
}

GenericDbAdapter.prototype = new BaseAdapter();
GenericDbAdapter.prototype.constructor = GenericDbAdapter;

/**
 * Set initial SELECT clause
 *
 * @param {string} select
 * @return {object}
 */
GenericDbAdapter.prototype.setSelect = function (select) {
    this.initialSelect = select;
    return this;
};

/**
 * Get initial SELECT clause
 *
 * @return {string}
 */
GenericDbAdapter.prototype.getSelect = function () {
    return this.initialSelect;
};

/**
 * Set initial FROM clause
 *
 * @param {string} from
 * @return {object}
 */
GenericDbAdapter.prototype.setFrom = function (from) {
    this.initialFrom = from;
    return this;
};

/**
 * Get initial FROM clause
 *
 * @return {string}
 */
GenericDbAdapter.prototype.getFrom = function () {
    return this.initialFrom;
};

/**
 * Set initial WHERE clause
 *
 * @param {string} where
 * @return {object}
 */
GenericDbAdapter.prototype.setWhere = function (where) {
    this.initialWhere = where;
    return this;
};

/**
 * Get initial WHERE clause
 *
 * @return {string}
 */
GenericDbAdapter.prototype.getWhere = function () {
    return this.initialWhere;
};

/**
 * Set initial parameters
 *
 * @param {array} params
 * @return {object}
 */
GenericDbAdapter.prototype.setParams = function (params) {
    this.initialParams = params;
    return this;
};

/**
 * Get initial parameters
 *
 * @return {array}
 */
GenericDbAdapter.prototype.getParams = function () {
    return this.initialParams;
};

/**
 * Check table and data
 *
 * @param {object} table
 * @throws Throws when error found
 * @return {object}
 */
GenericDbAdapter.prototype.check = function (table) {
    var columns = table.getColumns();
    for (var id in columns) {
        if (!columns.hasOwnProperty(id))
            continue;

        var params = columns[id];
        if (typeof params['sql_id'] == 'undefined')
            throw new Error("No 'sql_id' param for ID " + id);
    }

    var backupSqlAnds = clone(this.sqlAnds);
    var backupSqlParams = clone(this.sqlParams);

    var filters = table.getFilters();
    var successfulFilters = {};
    for (var column in filters) {
        if (!filters.hasOwnProperty(column))
            continue;

        var filterData = filters[column];
        var successfulNames = {};
        for (var name in filterData) {
            if (!filterData.hasOwnProperty(name))
                continue;

            var value = filterData[name];
            if (this.buildFilter(columns[column]['sql_id'], columns[column]['type'], name, value))
                successfulNames[name] = value;
        }
        if (Object.keys(successfulNames).length > 0)
            successfulFilters[column] = successfulNames;
    }
    table.setFilters(successfulFilters);

    this.sqlAnds = backupSqlAnds;
    this.sqlParams = backupSqlParams;
    return this;
};

/**
 * Filter data
 *
 * @param {object} table
 * @return {object}
 */
GenericDbAdapter.prototype.filter = function (table) {
    this.sqlAnds = {};
    this.sqlParams = clone(this.initialParams);

    var columns = table.getColumns();
    var filters = table.getFilters();
    for (var column in filters) {
        if (!filters.hasOwnProperty(column))
            continue;

        var filterData = filters[column];
        for (var name in filterData) {
            if (!filterData.hasOwnProperty(name))
                continue;

            var value = filterData[name];
            this.buildFilter(columns[column]['sql_id'], columns[column]['type'], name, value);
        }
    }

    return this;
}

/**
 * Sort data
 *
 * @param {object} table
 * @return {object}
 */
GenericDbAdapter.prototype.sort = function (table) {
    var column = table.getSortColumn();
    var dir = table.getSortDir();

    if (!column)
        return;

    var sqlId = null;
    var columns = table.getColumns();
    for (var id in columns) {
        if (!columns.hasOwnProperty(id))
            continue;

        var params = columns[id];
        if (id == column) {
            sqlId = params['sql_id'];
            break;
        }
    }

    if (!sqlId)
        throw new Error("No 'sql_id' for column: " + column);

    this.sqlOrderBy = sqlId + " " + dir;
    return this;
}

/**
 * Paginate and return result
 *
 * @param {object} table
 * @return {object} Returns promise
 */
GenericDbAdapter.prototype.paginate = function () {
    throw new Error("paginate() is not set");
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
GenericDbAdapter.prototype.buildFilter = function () {
    throw new Error("buildFilter() is not set");
};

module.exports = GenericDbAdapter;
