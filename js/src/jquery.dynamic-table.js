;(function ($, window, document, undefined) {

    var pluginName = "dynamicTable",
        dataKey = "plugin_" + pluginName;

    var _buildTable = function (plugin) {
        plugin.element.addClass('dynamic-table');

        $('<div></div>')
            .attr('class', 'overlay-back')
            .appendTo(plugin.element);

        $('<div></div>')
            .attr('class', 'overlay-loader')
            .css('background-image', 'url(' + plugin.options.loaderImage + ')')
            .appendTo(plugin.element);

        var table = $('<table></table>');
        table.attr('class', plugin.options.tableClass)
             .appendTo(plugin.element);

        var thead = $('<thead></thead>');
        thead.appendTo(table);

        var tr = $('<tr></tr>');
        tr.appendTo(thead);

        var visibleCounter = 0;

        if (plugin.options.rowIdColumn != null) {
            visibleCounter++;
            $('<th class="selector"><input type="checkbox"></th>')
                .appendTo(tr)
                .find('input')
                .prop('disabled', true);
        }

        $.each(plugin.columns, function (id, props) {
            if (props.visible)
                visibleCounter++;

            var th = $('<th></th>');
            th.attr('data-column-id', id)
              .css('display', props.visible ? 'table-cell' : 'none')
              .appendTo(tr)

            $('<span class="text"></span>')
                .text(props.title)
                .appendTo(th);

            $('<a class="link" href="#"></a>')
                .css('display', 'none')
                .text(props.title)
                .appendTo(th);

            $('<i class="sort-asc fa fa-sort-alpha-asc"></i>')
                .css('display', 'none')
                .appendTo(th);

            $('<i class="sort-desc fa fa-sort-alpha-desc"></i>')
                .css('display', 'none')
                .appendTo(th);

            $('<button class="filter btn btn-default btn-xs"></button>')
                .css('display', 'none')
                .html('<i class="fa fa-filter"></i>')
                .appendTo(th);
        });

        var tbodyEmpty = $('<tbody></tbody>');
        tbodyEmpty.attr('class', 'empty')
                  .appendTo(table);

        var tr = $('<tr></tr>')
        tr.appendTo(tbodyEmpty);

        var td = $('<td></td>')
        td.attr('colspan', visibleCounter)
          .text(plugin.options.strings.BANNER_LOADING)
          .html(td.html() + '<br><img src="' + plugin.options.loaderImage + '"><br>')
          .appendTo(tr);

        var tbodyData = $('<tbody></tbody>');
        tbodyData.attr('class', 'data')
                 .attr('display', 'none')
                 .appendTo(table);

        var tfoot = $('<tfoot></tfoot>');
        tfoot.appendTo(table);

        var tr = $('<tr></tr>');
        tr.appendTo(tfoot);

        var td = $('<td></td>');
        td.attr('colspan', visibleCounter)
          .appendTo(tr);

        var rightToolbar = $('<div></div>');
        rightToolbar.attr('class', 'pull-right btn-toolbar')
                    .appendTo(td);

        var dropdown = $('<div></div>');
        dropdown.attr('class', 'btn-group dropdown')
                .appendTo(rightToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default dropdown-toggle')
            .attr('data-toggle', 'dropdown')
            .html(plugin.options.strings.PAGE_SIZE + ' <span class="caret"></span>')
            .appendTo(dropdown);

        var list = $('<ul></ul>');
        list.attr('class', 'dropdown-menu')
            .attr('role', 'menu')
            .appendTo(dropdown);

        $.each([ 15, 30, 50, 100, 0 ], function (index, value) {
            var li = $('<li></li>');
            li.attr('role', 'presentation')
              .appendTo(list);
            if (value == plugin.pageSize)
                li.attr('class', 'active');

            $('<a></a>')
                .attr('role', 'menuitem')
                .attr('tabindex', '-1')
                .attr('href', 'javascript:void()')
                .attr('data-size', value)
                .text(value == 0 ? 'All' : value)
                .on('click', function() {
                    var el = $(this);
                    el.closest('ul')
                      .find('li')
                      .removeClass('active');
                    el.closest('li')
                      .addClass('active');

                    plugin.setSize(el.attr('data-size'));
                })
                .appendTo(li);
        });

/*
    <tfoot>
        <tr>
            <td colspan="{{ totalColumns() }}">
                <div class="pull-right btn-toolbar">
                    <div class="btn-group" dropdown is-open="setPageToggle">
                        <button class="btn btn-default dropdown-toggle" ng-disabled="model.disabled">
                            {{ 'PAGE_SIZE' | translate }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li ng-repeat="size in [15, 30, 50, 100, 300]" ng-class="{ active: model.page.size == size }">
                                <a href="" ng-click="setPage(1, size)">{{ size }}</a>
                            </li>
                            <li class="divider"></li>
                            <li ng-class="{ active: model.page.size == 0 }">
                                <a href="" ng-click="setPage(1, 0)">{{ 'ALL_RECORDS' | translate }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group" dropdown>
                        <button class="btn btn-default dropdown-toggle" ng-disabled="model.disabled">
                            {{ 'COLUMNS' | translate }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li ng-repeat="column in model.columns">
                                <a href="" ng-click="toggleHidden(column)">
                                    <i ng-show="column.hidden" class="fa fa-square-o"></i>
                                    <i ng-hide="column.hidden" class="fa fa-check-square-o"></i>
                                    {{ column.title | translate }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="btn-toolbar">
                    <div class="btn-group">
                        <button class="btn btn-default" ng-click="pageNumber = 1; refresh()" ng-disabled="model.disabled || model.page.number <= 1">
                            <span class="fa fa-fast-backward"></span>
                        </button>
                        <button class="btn btn-default" ng-click="pageNumber = pagePrevious; refresh()" ng-disabled="model.disabled || model.page.number <= 1">
                            <span class="fa fa-step-backward"></span>
                        </button>
                    </div>
                    <div class="btn-group">
                        <div class="input-group">
                            <span class="input-group-addon" ng-show="pageOf1.length">{{ pageOf1 }}</span>
                            <input type="text" class="form-control pagination-input" ng-model="model.page.number"
                                   number-only min="1" max="model.page.total" key-enter="refresh()" ng-disabled="model.disabled">
                            <span class="input-group-addon"i ng-show="pageOf2.length">{{ pageOf2 }}</span>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" ng-click="pageNumber = pageNext; refresh()" ng-disabled="model.disabled || model.page.number >= model.page.total">
                            <span class="fa fa-step-forward"></span>
                        </button>
                        <button class="btn btn-default" ng-click="pageNumber = model.page.total; refresh()" ng-disabled="model.disabled || model.page.number >= model.page.total">
                            <span class="fa fa-fast-forward"></span>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-default" ng-click="refresh()" ng-disabled="model.disabled">
                            {{ 'REFRESH_BUTTON' | translate }}
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
*/
    };

    var _enableColumn = function (plugin, id, enabled)
    {
        var props = plugin.columns[id];

        plugin.element.find('.selector input')
                      .prop('disabled', !enabled);

        var th = plugin.element.find('[data-column-id=' + id + ']');
        th.find('.text')
          .css('display', props.sortable ? 'none' : 'inline');
        th.find('.link')
          .css('display', props.sortable ? 'inline' : 'none');
        th.find('.sort-asc')
          .css(
              'display',
              plugin.sortColumn == id && plugin.sortDir == 'asc'
                  ? 'inline' : 'none'
          );
        th.find('.sort-desc')
          .css(
              'display',
              plugin.sortColumn == id && plugin.sortDir == 'desc'
                  ? 'inline' : 'none'
          );
        th.find('.filter')
          .css('display', props.filters.length > 0 ? 'inline' : 'none');
    };

    var _showData = function (plugin)
    {
        if (plugin.rows.length == 0) {
            var tbodyData = plugin.element.find('tbody.data');
            tbodyData.css('display', 'none');

            var tbodyEmpty = plugin.element.find('tbody.empty');
            tbodyEmpty.find('td').text(plugin.options.strings.BANNER_EMPTY);
            tbodyEmpty.css('display', 'table-row-group');

            return;
        }

        var tbodyData = $('<tbody></tbody>');
        tbodyData.attr('class', 'data');

        $.each(plugin.rows, function (index, row) {
            var tr = $('<tr></tr>');

            if (plugin.options.rowIdColumn != null) {
                var rowId = row[plugin.options.rowIdColumn];

                tr.attr('data-row-id', rowId);

                var selector = $('<th class="selector"><input type="checkbox"></th>');
                selector.appendTo(tr)
                        .find('input')
                        .prop('disabled', true)
                        .prop('value', rowId);
            }

            var content = plugin.options.mapper != null
                ? plugin.options.mapper(row) : row;

            $.each(plugin.columns, function (id, props) {
                var td = $('<td></td>');
                if (plugin.options.mapper == null)
                    td.text(content[id]);
                else
                    td.html(content[id]);
                td.attr('data-column-id', id)
                  .css('display', props.visible ? 'table-cell' : 'none')
                  .appendTo(tr)
            });

            tr.appendTo(tbodyData);
        });

        plugin.element.find('tbody.empty').css('display', 'none');
        plugin.element.find('tbody.data').replaceWith(tbodyData)
                                         .css('display', 'table-row-group');
    }

    var Plugin = function (element, options) {
        this.id = element.attr('id');
        this.element = element;
        this.options = {
            rowIdColumn: null,
            mapper: null,
            tableClass: 'table table-striped table-hover table-condensed',
            loaderImage: 'img/loader.gif',
            strings: {
                BANNER_LOADING: 'Loading... Please wait',
                BANNER_EMPTY: 'Nothing found',
                PAGE_SIZE: 'Page size',
            },
        };
        this.columns = [];
        this.rows = [];
        this.filters = {};
        this.sortColumn = null;
        this.sortDir = 'asc';
        this.pageNumber = 1;
        this.pageSize = 15;
        this.totalPages = 1;

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
                    plugin.refresh();
                }
            );
        },

        enable: function (enabled) {
            var plugin = this;

            $.each(this.columns, function (id, props) {
                _enableColumn(plugin, id, enabled);
            });

            var tbody = plugin.element.find('tbody.data');
            if (tbody.css('display') == 'none')
                return;

            var pos = tbody.position();
            $.each([ '.overlay-back', '.overlay-loader' ], function (index, value) {
                var overlay = plugin.element.find(value);
                overlay.width(tbody.width())
                       .height(tbody.height())
                       .css('top', pos.top)
                       .css('left', pos.left)
                       .css('display', enabled ? 'none' : 'block');
            });
        },

        refresh: function () {
            var plugin = this;
            plugin.enable(false);

            $.getJSON(
                this.options.url,
                {
                    query: 'data',
                    filters: JSON.stringify(plugin.filters),
                    sort_column: JSON.stringify(plugin.sortColumn),
                    sort_dir: JSON.stringify(plugin.sortDir),
                    page_number: JSON.stringify(plugin.pageNumber),
                    page_size: JSON.stringify(plugin.pageSize),
                },
                function (data) {
                    if (data.success !== true) {
                        plugin.enable(true);
                        return;
                    }

                    plugin.sortColumn = data.sort_column;
                    plugin.sortDir = data.sort_dir;
                    plugin.pageNumber = data.page_number;
                    plugin.pageSize = data.page_size;
                    plugin.totalPages = data.total_pages;
                    plugin.filters = data.filters;
                    plugin.rows = data.rows;

                    _showData(plugin);
                    plugin.enable(true);
                }
            );
        },

        setSize: function (size) {
            this.pageSize = size;
            this.refresh();
        },
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
