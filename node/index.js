/**
 * Dynamic Table
 */

function DynamicTable() {}

DynamicTable.prototype.table = function () {
    var Table = require('./table.js');
    return Table;
};

DynamicTable.prototype.arrayAdapter = function () {
    var Adapter = require('./adapter/array.js');
    return Adapter;
};

DynamicTable.prototype.mysqlAdapter = function () {
    var Adapter = require('./adapter/mysql.js');
    return Adapter;
};

DynamicTable.prototype.pgAdapter = function () {
    var Adapter = require('./adapter/pg.js');
    return Adapter;
};

module.exports = new DynamicTable();
