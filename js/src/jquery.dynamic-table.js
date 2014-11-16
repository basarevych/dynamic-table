;(function ($, window, document, undefined) {

    var pluginName = "dynamicTable",
        dataKey = "plugin_" + pluginName;

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
                BUTTON_PAGE_SIZE: 'Page size',
                BUTTON_COLUMNS: 'Columns',
                BUTTON_REFRESH: 'Refresh',
                LABEL_PAGE_OF_1: 'Page',
                LABEL_PAGE_OF_2: 'of {0}',
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

        enable: function (enable) {
            var plugin = this;

            $.each(this.columns, function (id, props) {
                _enableColumnControls(plugin, id, enable);
            });

            _enableOverlay(plugin, !enable);

            _enablePaginator(plugin, enable);
        },

        refresh: function (override) {
            var plugin = this;
            plugin.enable(false);

            var data = {
                query: 'data',
                filters: JSON.stringify(plugin.filters),
                sort_column: JSON.stringify(plugin.sortColumn),
                sort_dir: JSON.stringify(plugin.sortDir),
                page_number: JSON.stringify(plugin.pageNumber),
                page_size: JSON.stringify(plugin.pageSize),
            };
            if (override != undefined) {
                $.each(override, function (key, value) {
                    data[key] = JSON.stringify(value);
                });
            }

            $.getJSON(this.options.url, data, function (data) {
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
            });
        },

        setSize: function (size) {
            this.refresh({ page_size: size });
        },

        setPage: function (page) {
            page = parseInt(page);
            if (page < 1)
                page = 1;
            else if (page > this.totalPages)
                page = this.totalPages;

            this.refresh({ page_number: page });
        },

        toggleColumn: function (column) {
            var plugin = this, visibleCounter = 0, visible;

            if (this.options.rowIdColumn != null)
                visibleCounter++;

            $.each(this.columns, function (id, props) {
                if (id == column) {
                    visible = !props.visible;
                    plugin.columns[id].visible = visible;
                    if (visible)
                        visibleCounter++;
                } else {
                    if (props.visible)
                        visibleCounter++;
                }
            });

            this.element.find('thead th[data-column-id=' + column + ']')
                        .css('display', visible ? 'table-cell' : 'none');
            this.element.find('tbody td[data-column-id=' + column + ']')
                        .css('display', visible ? 'table-cell' : 'none');
            this.element.find('thead.empty td')
                        .prop('colspan', visibleCounter);
            this.element.find('tfoot td')
                        .prop('colspan', visibleCounter);
            this.element.find('tfoot a[data-column-id=' + column + '] i')
                        .attr('class', 'fa ' + (visible ? 'fa-check-square-o' : 'fa-square-o'));
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
                    .attr('role', 'toolbar')
                    .appendTo(td);

        var dropdown = $('<div></div>');
        dropdown.attr('class', 'btn-group dropdown dropup')
                .attr('role', 'group')
                .appendTo(rightToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default dropdown-toggle')
            .attr('data-toggle', 'dropdown')
            .html(plugin.options.strings.BUTTON_PAGE_SIZE + ' <span class="caret"></span>')
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
                .attr('href', 'javascript:void(0)')
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

        var dropdown = $('<div></div>');
        dropdown.attr('class', 'btn-group dropdown dropup')
                .attr('role', 'group')
                .appendTo(rightToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default dropdown-toggle')
            .attr('data-toggle', 'dropdown')
            .html(plugin.options.strings.BUTTON_COLUMNS + ' <span class="caret"></span>')
            .appendTo(dropdown);

        var list = $('<ul></ul>');
        list.attr('class', 'dropdown-menu')
            .attr('role', 'menu')
            .appendTo(dropdown);

        $.each(plugin.columns, function (id, column) {
            var li = $('<li></li>');
            li.attr('role', 'presentation')
              .appendTo(list);

            var span = $('<span></span>');
            span.text(column.title);

            $('<a></a>')
                .attr('role', 'menuitem')
                .attr('tabindex', '-1')
                .attr('href', 'javascript:void(0)')
                .attr('data-column-id', id)
                .html('<i class="fa ' + (column.visible ? 'fa-check-square-o' : 'fa-square-o') + '"></i> ' + span.html())
                .on('click', function() {
                    plugin.toggleColumn($(this).attr('data-column-id'));
                })
                .appendTo(li);
        });

        var leftToolbar = $('<div></div>');
        leftToolbar.attr('class', 'btn-toolbar')
                    .attr('role', 'toolbar')
                    .appendTo(td);

        var group = $('<div></div>');
        group.attr('class', 'btn-group')
             .attr('role', 'group')
             .appendTo(leftToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'first')
            .html('<i class="fa fa-fast-backward"></i>')
            .on('click', function () {
                plugin.setPage(1);
            })
            .appendTo(group);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'previous')
            .html('<i class="fa fa-step-backward"></i>')
            .on('click', function () {
                plugin.setPage(plugin.pageNumber - 1);
            })
            .appendTo(group);

        var group = $('<div></div>');
        group.attr('class', 'btn-group')
             .attr('role', 'group')
             .appendTo(leftToolbar);

        var inputGroup = $('<div></div>');
        inputGroup.attr('class', 'input-group')
                  .appendTo(group);

        $('<span></span>')
            .attr('class', 'input-group-addon')
            .text(plugin.options.strings.LABEL_PAGE_OF_1)
            .appendTo(inputGroup);

        $('<input>')
            .attr('class', 'form-control pagination-input')
            .on('keypress', function (event) {
                if (event.keyCode == 13)
                    plugin.setPage($(this).val());
                else if (event.keyCode < 48 || event.keyCode > 57)
                    event.preventDefault();
            })
            .on('keyup', function () {
                if ($(this).val() != plugin.pageNumber) {
                    plugin.element.find('tfoot button[data-action=refresh]')
                                  .addClass('btn-primary')
                                  .removeClass('btn-default');
                } else {
                    plugin.element.find('tfoot button[data-action=refresh]')
                                  .addClass('btn-default')
                                  .removeClass('btn-primary');
                }
            })
            .appendTo(inputGroup);

        $('<span></span>')
            .attr('class', 'input-group-addon')
            .text(plugin.options.strings.LABEL_PAGE_OF_2.replace('{0}', plugin.totalPages))
            .appendTo(inputGroup);

        var group = $('<div></div>');
        group.attr('class', 'btn-group')
             .attr('role', 'group')
             .appendTo(leftToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'next')
            .html('<i class="fa fa-step-forward"></i>')
            .on('click', function () {
                plugin.setPage(plugin.pageNumber + 1);
            })
            .appendTo(group);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'last')
            .html('<i class="fa fa-fast-forward"></i>')
            .on('click', function () {
                plugin.setPage(plugin.totalPages);
            })
            .appendTo(group);

        var group = $('<div></div>');
        group.attr('class', 'btn-group')
             .attr('role', 'group')
             .appendTo(leftToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'refresh')
            .text(plugin.options.strings.BUTTON_REFRESH)
            .on('click', function () {
                var input = plugin.element.find('tfoot input.pagination-input');
                plugin.refresh({ page_number: input.val() });
            })
            .appendTo(group);
    };

    var _enableColumnControls = function (plugin, id, enable)
    {
        var props = plugin.columns[id];

        plugin.element.find('.selector input')
                      .prop('disabled', !enable);

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

    var _enableOverlay = function (plugin, enable) {
        var tbody = plugin.element.find('tbody.data');
        var pos = tbody.position();
        $.each([ '.overlay-back', '.overlay-loader' ], function (index, value) {
            var overlay = plugin.element.find(value);
            overlay.width(tbody.width())
                   .height(tbody.height())
                   .css('top', pos.top)
                   .css('left', pos.left)
                   .css('display', enable ? 'block' : 'none');
        });
    };

    var _enablePaginator = function (plugin, enable)
    {
        var disabled = (!enable || plugin.pageNumber == 1);
        plugin.element.find('tfoot button[data-action=first]')
                      .prop('disabled', disabled);
        plugin.element.find('tfoot button[data-action=previous]')
                      .prop('disabled', disabled);

        var disabled = (!enable || plugin.pageNumber == plugin.totalPages);
        plugin.element.find('tfoot button[data-action=next]')
                      .prop('disabled', disabled);
        plugin.element.find('tfoot button[data-action=last]')
                      .prop('disabled', disabled);

        plugin.element.find('tfoot button[data-action=refresh]')
                      .prop('disabled', !enable)
                      .addClass('btn-default')
                      .removeClass('btn-primary');

        plugin.element.find('tfoot .pagination-input')
                      .prop('disabled', !enable);
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

        plugin.element.find('.pagination-input')
                      .val(plugin.pageNumber);
    };

}(jQuery, window, document));
