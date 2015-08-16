/**
 * Base class for adapters
 */

'use strict';

function Base() {
}

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
