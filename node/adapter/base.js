/**
 * Base class for adapters
 */

'use strict';

function Base() {
    this.dbTimezone = null;     // Data source timezone
}

/**
 * DB timezone setter
 *
 * @param {string} timezone     New timezone
 * @retrurn {object}            Returns self
 */
Base.prototype.setDbTimezone = function (timezone) {
    this.dbTimezone = timezone;
    return this;
};

/**
 * DB timezone getter
 *
 * @return {string|null}        Returns current timezone or null
 */
Base.prototype.getDbTimezone = function () {
    return this.dbTimezone;
};

/**
 * Check table and data
 *
 * @param {object} table
 */
Base.prototype.check = function () {
    throw new Error("check() is not defined");
};

/**
 * Filter data
 *
 * @param {object} table
 */
Base.prototype.filter = function () {
    throw new Error("filter() is not defined");
};

/**
 * Sort data
 *
 * @param {object} table
 */
Base.prototype.sort = function () {
    throw new Error("sort() is not defined");
};

/**
 * Paginate and return data
 *
 * @param {object} table
 * @return {object}
 */
Base.prototype.paginate = function () {
    throw new Error("paginate() is not defined");
};

module.exports = Base;
