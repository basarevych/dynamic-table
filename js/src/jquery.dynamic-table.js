;(function ($, window, document, undefined) {

    var pluginName = "dynamicTable",
        dataKey = "plugin_" + pluginName;

    var _buildTable = function (plugin) {
        var table = $('<table><thead><tr></tr></thead></table');
        table.attr('class', plugin.options.tableClass)
             .appendTo(plugin.element);
        var allSelector = $('<th class="selector"><input type="checkbox"></th>');
        allSelector.appendTo(table.find('thead tr'));

/*

    <thead>
        <tr>
            <th class="selector" ng-show="model.selectable">
                <input type="checkbox" ng-click="selectAll()"
                       ng-checked="model.selected.length == model.rows.length" ng-disabled="model.disabled">
            </th>
            <th ng-repeat="column in model.columns | filter:notHidden">
                <span ng-switch on="column.sortable && !model.disabled">
                    <a href="" ng-switch-when="true" ng-click="sort(column.name)">{{ column.title | translate }}</a>
                    <span ng-switch-when="false">{{ column.title | translate }}</span>
                </span>
                <span ng-show="column.name == model.sort.name">
                    <i ng-show="model.sort.dir == 'asc'" class="fa fa-sort-alpha-asc"></i>
                    <i ng-show="model.sort.dir == 'desc'" class="fa fa-sort-alpha-desc"></i>
                </span>
                <button class="filter btn btn-default btn-xs" ng-show="column.filterable" ng-disabled="model.disabled" ng-click="setFilter(column)">
                    <i class="fa fa-filter"></i>
                </button>
            </th>
        </tr>
    </thead>
*/
        $.each(plugin.columns, function (id, props) {
console.log(props);
        });
    };

    var Plugin = function (element, options) {
        this.id = element.attr('id');
        this.element = element;
        this.options = {
            tableClass: 'table table-striped table-hover table-condensed',
        };
        this.columns = [];

        this.init(options);
    };

    Plugin.prototype = {
        init: function (options) {
            $.extend(this.options, options);

            var plugin = this;
            $.getJSON(
                this.options.url,
                { query: 'describe' },
                function (data) {
                    if (data.success !== true)
                        return;
                    
                    plugin.columns = data.columns;
                    _buildTable(plugin);
                }
            );
        },

        refresh: function () {
        }
    };

    $.fn[pluginName] = function (options) {
        var plugin = this.data(dataKey);
        if (plugin instanceof Plugin) {
            if (typeof options !== 'undefined')
                plugin.init(options);
        } else {
            plugin = new Plugin(this, options);
            this.data(dataKey, plugin);
        }

        return plugin;
    };

}(jQuery, window, document));
