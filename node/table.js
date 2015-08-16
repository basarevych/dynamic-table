/**
 * The Table class
 */

'use strict';

var q = require('q');

function Table() {
    /**
     * Data adapter
     */
    this.adapter = null;

    /**
     * Table columns
     *
     * columns = {
     *     columnId: columnParams,
     *     // ...
     * };
     * columnParams = {
     *     title: columnTitle,
     *     type: columnType, // One of TYPE_* constants
     *     filters: columnFilters, // Array of FILTER_* constants
     *     sortable: true|false,
     *     visible: true|false,
     * };
     */
    this.columns = {};

    /**
     * Data row -> result row mapper
     *
     * Function should get single argument "row", which is data row
     * and return an object { column_id: column_value, // ... }
     * which is feeded to the front-end
     */
    this.mapper = null;

    /**
     * Query filters
     *
     * filters = {
     *     columnId: filterParams,
     *     // ...
     * };
     * filterParams = {
     *     filter: 'value', // filter is one of FILTER_* constants
     *     // ...
     * };
     */
    this.filters = {};

    /**
     * Query sort column name
     */
    this.sortColumn = null;

    /**
     * Query sort direction (asc|desc)
     */
    this.sortDir = Table.DIR_ASC;

    /**
     * Number of current page
     */
    this.pageNumber = 1;

    /**
     * Number of records per page. 0 = all records (one page)
     */
    this.pageSize = Table.PAGE_SIZE;

    /**
     * Total number of pages
     */
    this.totalPages = 1;

}

/**
 * Available column types
 */
Table.TYPE_STRING = 'string';
Table.TYPE_INTEGER = 'integer';
Table.TYPE_FLOAT = 'float';
Table.TYPE_BOOLEAN = 'boolean';
Table.TYPE_DATETIME = 'datetime';

/**
 * Available filters
 */
Table.FILTER_LIKE = 'like';
Table.FILTER_EQUAL = 'equal';
Table.FILTER_BETWEEN = 'between';
Table.FILTER_NULL = 'null';

/**
 * Sorting directions
 */
Table.DIR_ASC = 'asc';
Table.DIR_DESC = 'desc';

/**
 * Default page size
 */
Table.PAGE_SIZE = 15;

/**
 * List column types
 *
 * @return {array}
 */
Table.getAvailableTypes = function () {
    return [
        Table.TYPE_STRING,
        Table.TYPE_INTEGER,
        Table.TYPE_FLOAT,
        Table.TYPE_BOOLEAN,
        Table.TYPE_DATETIME,
    ];
};

/**
 * List filter types
 *
 * @return {array}
 */
Table.getAvailableFilters = function () {
    return [
        Table.FILTER_LIKE,
        Table.FILTER_EQUAL,
        Table.FILTER_BETWEEN,
        Table.FILTER_NULL,
    ];
};

/**
 * Adapter setter
 *
 * @param {object} adapter
 * @return {object}
 */
Table.prototype.setAdapter = function (adapter) {
    this.adapter = adapter;
    return this;
};

/**
 * Adapter getter
 *
 * @return {object}
 */
Table.prototype.getAdapter = function () {
    return this.adapter;
};

/**
 * Columns setter
 *
 * @param {object} columns
 * @throws In case of invalid column definition
 * @return {object}
 */
Table.prototype.setColumns = function (columns) {
    for (var id in columns) {
        if (!columns.hasOwnProperty(id))
            continue;

        var params = columns[id];
        if (typeof params['title'] == 'undefined')
            throw new Error("No 'title' param for ID " + id);
        if (typeof params['type'] == 'undefined')
            throw new Error("No 'type' param for ID " + id);
        if (Table.getAvailableTypes().indexOf(params['type']) == -1)
            throw new Error("Unknown type '" + params['type'] + "' for ID " + id);
        if (typeof params['filters'] == 'undefined')
            throw new Error("No 'filters' param for ID " + id);
        if (typeof params['sortable'] == 'undefined')
            throw new Error("No 'sortable' param for ID " + id);
        if (typeof params['visible'] == 'undefined')
            throw new Error("No 'visible' param for ID " + id);

        params['filters'].forEach(function (filter) {
            if (Table.getAvailableFilters().indexOf(filter) == -1)
                throw new Error("Unknown filter: " + filter);
        });
    }

    this.columns = columns;
    return this;
};

/**
 * Columns getter
 *
 * @return {object}
 */
Table.prototype.getColumns = function () {
    return this.columns;
};

/**
 * Mapper setter
 *
 * @param {function} mapper
 * @return {object}
 */
Table.prototype.setMapper = function (mapper) {
    this.mapper = mapper;
    return this;
};

/**
 * Mapper getter
 *
 * @return {function}
 */
Table.prototype.getMapper = function () {
    return this.mapper;
};

/**
 * Filters setter
 *
 * @param {object} filters
 * @return {object}
 */
Table.prototype.setFilters = function (filters) {
    if (filters === null)
        filters = {};

    var result = {};
    for (var column in filters) {
        if (!filters.hasOwnProperty(column))
            continue;

        var filter = filters[column];
        var found = {};
        for (var id in this.columns) {
            if (!this.columns.hasOwnProperty(id))
                continue;

            var params = this.columns[id];
            if (id == column) {
                for (var name in filter) {
                    if (!filter.hasOwnProperty(name))
                        continue;

                    var value = filter[name];
                    if (params['filters'].indexOf(name) != -1)
                        found[name] = value;
                }
                break;
            }
        }

        if (Object.keys(found).length > 0)
            result[column] = found;
    }

    this.filters = result;
    return this;
};

/**
 * Filters getter
 *
 * @return {object}
 */
Table.prototype.getFilters = function () {
    return this.filters;
};

/**
 * Sort column setter
 *
 * @param {string} column
 * @return {object}
 */
Table.prototype.setSortColumn = function (column) {
    this.sortColumn = null;

    var found = false;
    for (var id in this.columns) {
        if (!this.columns.hasOwnProperty(id))
            continue;

        var params = this.columns[id];
        if (id == column) {
            found = (params['sortable'] === true);
            break;
        }
    }

    if (found)
        this.sortColumn = column;

    return this;
};

/**
 * Sort column getter
 *
 * @return {string}
 */
Table.prototype.getSortColumn = function () {
    return this.sortColumn;
};

/**
 * Sort direction setter
 *
 * @param {string} dir
 * @return {object}
 */
Table.prototype.setSortDir = function (dir) {
    if ([ Table.DIR_ASC, Table.DIR_DESC ].indexOf(dir) == -1)
        this.sortDir = Table.DIR_ASC;
    else
        this.sortDir = dir;

    return this;
};

/**
 * Sort direction getter
 *
 * @return {string}
 */
Table.prototype.getSortDir = function () {
    return this.sortDir;
};

/**
 * Page number setter
 *
 * @param {number} number
 * @return {object}
 */
Table.prototype.setPageNumber = function (number) {
    this.pageNumber = (number === null ? 1 : parseInt(number, 10));
    if (this.pageNumber < 1)
        this.pageNumber = 1;

    return this;
};

/**
 * Page number getter
 *
 * @return {number}
 */
Table.prototype.getPageNumber = function () {
    return this.pageNumber;
};

/**
 * Page size setter
 *
 * @param {number} size
 * @return {object}
 */
Table.prototype.setPageSize = function (size)
{
    this.pageSize = (size === null ? Table.PAGE_SIZE : parseInt(size, 10));
    if (this.pageSize < 0)
        this.pageSize = Table.PAGE_SIZE;

    return this;
};

/**
 * Page size getter
 *
 * @return {number}
 */
Table.prototype.getPageSize = function () {
    return this.pageSize;
};

/**
 * Total pages number getter
 *
 * @return {number}
 */
Table.prototype.getTotalPages = function () {
    return this.totalPages;
};

/**
 * Sets sort, filter and pagination options
 *
 * @param {object} params
 * @return {object}
 */
Table.prototype.setPageParams = function (params) {
    this.setFilters(JSON.parse(params['filters']));
    this.setSortColumn(JSON.parse(params['sort_column']));
    this.setSortDir(JSON.parse(params['sort_dir']));
    this.setPageNumber(JSON.parse(params['page_number']));
    this.setPageSize(JSON.parse(params['page_size']));

    return this;
};

/**
 * Recalculates pageNumber and totalPages
 *
 * @param {number} rowCount
 * @return {object}
 */
Table.prototype.calculatePageParams = function (rowCount) {
    this.totalPages = this.pageSize > 0 ? Math.ceil(rowCount / this.pageSize) : 1;
    if (this.totalPages <= 0)
        this.totalPages = 1;
    if (this.pageNumber > this.totalPages)
        this.pageNumber = this.totalPages;

    return this;
};

/**
 * Return table description
 *
 * @param {function} cb
 */
Table.prototype.describe = function (cb) {
    var columns = {};
    for (var id in this.columns) {
        if (!this.columns.hasOwnProperty(id))
            continue;

        var params = this.columns[id];
        columns[id] = {
            title: params['title'],
            type: params['type'],
            filters: params['filters'],
            sortable: params['sortable'],
            visible: params['visible'],
        };
    }

    cb(null, { columns: columns });
};

/**
 * Fetch data and feed it to front-end
 *
 * @param {function} cb
 */
Table.prototype.fetch = function (cb) {
    var adapter = this.getAdapter();
    if (!adapter)
        throw new Error("Adapter property is not set");

    adapter.check(this);
    adapter.filter(this);
    adapter.sort(this);

    var me = this;
    var defer = q.defer();
    adapter.paginate(this)
        .then(function (result) {
            cb(null, {
                sort_column: me.getSortColumn(),
                sort_dir: me.getSortDir(),
                page_number: me.getPageNumber(),
                page_size: me.getPageSize(),
                total_pages: me.getTotalPages(),
                filters: me.getFilters(),
                rows: result,
            });
        })
        .catch(function (err) {
            cb(err);
        });
};

module.exports = Table;
