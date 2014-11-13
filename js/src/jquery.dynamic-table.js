;(function ($, window, document, undefined) {

    var pluginName = "dynamicTable",
        dataKey = "plugin_" + pluginName;

    var _buildTable = function (plugin) {
        plugin.element.addClass('dynamic-table');

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
            var allSelector = $('<th class="selector"><input type="checkbox"></th>');
            allSelector.appendTo(tr)
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

            var text = $('<span class="text"></span>');
            text.text(props.title)
                .appendTo(th);

            var link = $('<a class="link" href="#"></a>');
            link.css('display', 'none')
                .text(props.title)
                .appendTo(th);

            var sortAsc = $('<i class="sort-asc fa fa-sort-alpha-asc"></i>');
            sortAsc.css('display', 'none')
                   .appendTo(th);

            var sortDesc = $('<i class="sort-desc fa fa-sort-alpha-desc"></i>');
            sortDesc.css('display', 'none')
                    .appendTo(th);

            var filter = $('<button class="filter btn btn-default btn-xs"></button>');
            filter.css('display', 'none')
                  .html('<i class="fa fa-filter"></i>')
                  .appendTo(th);
        });

        var tbodyEmpty = $('<tbody></tbody>');
        tbodyEmpty.attr('class', 'empty')
                  .appendTo(table);

        var tr = $('<tr></tr>')
        tr.appendTo(tbodyEmpty);

        var td = $('<td></td>');
        td.attr('colspan', visibleCounter)
          .text(plugin.options.strings.BANNER_LOADING)
          .html(td.html() + '<br><img src="img/loader.gif"><br>')
          .appendTo(tr);

        var tbodyData = $('<tbody></tbody>');
        tbodyData.attr('class', 'data')
                 .attr('display', 'none')
                 .appendTo(table);
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
            strings: {
                BANNER_LOADING: 'Loading... Please wait',
                BANNER_EMPTY: 'Nothing found',
            },
        };
        this.columns = [];
        this.rows = [];
        this.filters = [];
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
/*
                var thead = plugin.element.find('thead');
                var pos = thead.position();
                var overlay = $('<div style="background: #000000; opacity: 0.5; position: absolute; z-index: 999"></div>');
                overlay.width(thead.width())
                       .height(thead.height())
                       .css('top', pos.top)
                       .css('left', pos.left)
                       .prependTo(plugin.element);
*/
            });
        },

        refresh: function () {
            var plugin = this;
            plugin.enable(false);

            $.getJSON(
                this.options.url,
                { query: 'data' },
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
