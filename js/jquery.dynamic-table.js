;(function ($, window, document, undefined) {

    var pluginName = "dynamicTable";

    var Plugin = function (element, options) {
        this.id = element.attr('id');
        this.element = element;
        this.options = {
            row_id_column: null,
            mapper: null,
            sort_column: null,
            sort_dir: 'asc',
            page_number: 1,
            page_size: 15,
            page_sizes: [ 15, 30, 50, 100, 0 ],
            table_class: 'table table-striped table-hover table-condensed',
            loader_image: 'img/loader.gif',
            strings: {
                BANNER_LOADING: 'Loading... Please wait',
                BANNER_EMPTY: 'Nothing found',
                BUTTON_PAGE_SIZE: 'Page size',
                BUTTON_COLUMNS: 'Columns',
                BUTTON_REFRESH: 'Refresh',
                BUTTON_OK: 'OK',
                BUTTON_CLEAR: 'Clear',
                BUTTON_CANCEL: 'Cancel',
                TITLE_FILTER_WINDOW: 'Filter',
                LABEL_PAGE_OF_1: 'Page',
                LABEL_PAGE_OF_2: 'of {0}',
                LABEL_FILTER_LIKE: 'Strings like',
                LABEL_FILTER_EQUAL: 'Values equal to',
                LABEL_FILTER_BETWEEN_START: 'Values greater than or equal to',
                LABEL_FILTER_BETWEEN_END: 'Values less than or equal to',
                LABEL_FILTER_NULL: 'Include rows with empty value in this column',
                LABEL_TRUE: 'True',
                LABEL_FALSE: 'False',
                DATE_TIME_FORMAT: 'YYYY-MM-DD HH:mm:ss',
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
        this.visibleColumns = 0;

        this.init(options);
    };

    Plugin.prototype = {
        init: function (options) {
            $.extend(this.options, options);

            this.sortColumn = this.options.sort_column;
            this.sortDir = this.options.sort_dir;
            this.pageNumber = this.options.page_number;
            this.pageSize = this.options.page_size;

            _createTable(this);

            var plugin = this;
            $.getJSON(
                this.options.url,
                { query: 'describe' },
                function (data) {
                    if (data.success !== true)
                        return;
                    
                    plugin.columns = data.columns;
                    _initTable(plugin);
                    plugin.refresh();
                }
            );
        },

        refresh: function (params) {
            var plugin = this;
            plugin.element.trigger('dt.loading');
            plugin.enable(false);

            var data = {
                query: 'data',
                filters: JSON.stringify(plugin.filters),
                sort_column: JSON.stringify(plugin.sortColumn),
                sort_dir: JSON.stringify(plugin.sortDir),
                page_number: JSON.stringify(plugin.pageNumber),
                page_size: JSON.stringify(plugin.pageSize),
            };
            if (typeof params != 'undefined') {
                $.each(params, function (key, value) {
                    data[key] = JSON.stringify(value);
                });
            }

            var selected = this.element.find('tbody td.selector input:checked');

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

                $.each(selected, function (index, element) {
                    plugin.toggleSelected($(element).val());
                });

                plugin.enable(true);
                plugin.element.trigger('dt.loaded');
            });
        },

        enable: function (enable) {
            var plugin = this;

            $.each(this.columns, function (id, props) {
                _enableColumnControls(plugin, id, enable);
            });

            _enableOverlay(plugin, !enable);

            _enablePaginator(plugin, enable);
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

        setFilters: function (id, filters) {
            var data = this.filters;
            data[id] = filters;
            this.refresh({ filters: data });
        },

        toggleSort: function (id) {
            var dir = 'asc';
            if (this.sortColumn == id)
                dir = (this.sortDir == 'asc' ? 'desc' : 'asc');
            this.refresh({ sort_column: id, sort_dir: dir });
        },

        toggleColumn: function (id) {
            var props = this.columns[id];
            var visible = (this.columns[id].visible = !props.visible);

            if (visible)
                this.visibleColumns++;
            else
                this.visibleColumns--;

            this.element.find('thead th[data-column-id=' + id + ']')
                        .css('display', visible ? 'table-cell' : 'none');
            this.element.find('tbody td[data-column-id=' + id + ']')
                        .css('display', visible ? 'table-cell' : 'none');
            this.element.find('thead.empty td')
                        .prop('colspan', this.visibleColumns);
            this.element.find('tfoot td')
                        .prop('colspan', this.visibleColumns);
            this.element.find('tfoot a[data-column-id=' + id + '] span.glyphicon')
                        .attr('class', 'glyphicon glyphicon-ok ' + (visible ? '' : 'invisible'));
        },

        toggleSelected: function (rowId) {
            var input = $('tbody tr[data-row-id=' + rowId + '] td.selector input'),
                checked = !input.prop('checked');

            input.prop('checked', checked);
            if (checked) {
                input.closest('tr').addClass('success');
                this.element.trigger('dt.selected');
            } else {
                input.closest('tr').removeClass('success');
                this.element.trigger('dt.deselected');
            }

            var all = this.element.find('tbody.data td.selector input:checked');
            this.element.find('thead th.selector input')
                        .prop('checked', all.length == this.rows.length);
        },

        getSelected: function () {
            var inputs = this.element.find('tbody.data td.selector input:checked');
            var ids = [];
            inputs.each(function (index, el) {
                ids.push($(el).val());
            });

            return ids;
        },
    };

    $.fn[pluginName] = function (options) {
        var plugin = this.data(pluginName);
        if (plugin instanceof Plugin) {
            if (typeof options !== 'undefined')
                plugin.init(options);
        } else {
            plugin = new Plugin(this, options);
            this.data(pluginName, plugin);
        }

        return plugin;
    };

    var _createTable = function (plugin) {
        plugin.element.empty()
                      .addClass('dynamic-table');

        var table = $('<table></table>');
        table.attr('class', plugin.options.table_class)
             .css('display', 'none')
             .appendTo(plugin.element);

        $('<div></div>')
            .attr('class', 'overlay-back')
            .appendTo(plugin.element);

        $('<div></div>')
            .attr('class', 'overlay-loader')
            .css('background-image', 'url(' + plugin.options.loader_image + ')')
            .appendTo(plugin.element);

        var loader = $('<div></div>');
        loader.attr('class', 'table-loader')
              .css('text-align', 'center')
              .text(plugin.options.strings.BANNER_LOADING)
              .html(loader.html() + '<br><img src="' + plugin.options.loader_image + '"><br>')
              .appendTo(plugin.element);
    };

    var _initTable = function (plugin) {
        _initThead(plugin);
        _initTbody(plugin);
        _initTfoot(plugin);
    };

    var _initThead = function (plugin) {
        var thead = $('<thead></thead>');

        var tr = $('<tr></tr>');
        tr.appendTo(thead);

        plugin.visibleColumns = 0;
        if (plugin.options.row_id_column != null) {
            plugin.visibleColumns++;

            $('<th class="selector"><input type="checkbox"></th>')
                .appendTo(tr)
                .find('input')
                .prop('disabled', true)
                .on('change', function () {
                    var inputs = plugin.element.find('tbody td.selector input');
                    if ($(this).prop('checked')) {
                        inputs.prop('checked', true)
                              .closest('tr')
                              .addClass('success');
                        plugin.element.trigger('dt.selected');
                    } else {
                        inputs.prop('checked', false)
                              .closest('tr')
                              .removeClass('success');
                        plugin.element.trigger('dt.deselected');
                    }
                });
        }

        $.each(plugin.columns, function (id, props) {
            if (props.visible)
                plugin.visibleColumns++;

            var th = $('<th></th>');
            th.attr('data-column-id', id)
              .css('display', props.visible ? 'table-cell' : 'none')
              .appendTo(tr)

            $('<span class="text"></span>')
                .text(props.title)
                .appendTo(th);

            $('<a class="link"></a>')
                .css('display', 'none')
                .attr('href', 'javascript:void(0)')
                .text(props.title)
                .on('click', function () {
                    plugin.toggleSort(id);
                })
                .appendTo(th);

            $('<span class="sort-asc glyphicon glyphicon-sort-by-attributes"></span>')
                .css('display', 'none')
                .appendTo(th);

            $('<span class="sort-desc glyphicon glyphicon-sort-by-attributes-alt"></span>')
                .css('display', 'none')
                .appendTo(th);

            if (props.filters.length == 0)
                return;
 
            $('<button class="filter btn btn-default btn-xs"></button>')
                .css('display', 'none')
                .html('<span class="glyphicon glyphicon-wrench"></span>')
                .on('click', function () {
                    var popover = $(this).parent().find('.popover');
                    var th = $(this).closest('th');
                    var posTh = th.position(), posThead = thead.position();
                    var left = posTh.left;
                    var visible = popover.is(':visible');

                    thead.find('.popover').css('display', 'none');

                    if (posTh.left + popover.width() > posThead.left + thead.width())
                        left -= (popover.width() - th.width());

                    popover.css('top', posTh.top + $(this).height() + 15)
                           .css('left', left)
                           .css('display', visible ? 'none' : 'block');
                })
                .appendTo(th);

            var popover = $('<div></div>');
            popover.attr('class', 'popover')
                   .appendTo(th);

            $('<h3></h3>')
                .attr('class', 'popover-title')
                .text(plugin.options.strings.TITLE_FILTER_WINDOW)
                .appendTo(popover);

            var formWrapper = $('<div></div>');
            formWrapper.attr('class', 'popover-content')
                       .appendTo(popover);

            var form = $('<form onsubmit="return false"></form>');
            form.attr('role', 'form')
                .appendTo(formWrapper);

            if (props.filters.indexOf('like') != -1) {
                var group = $('<div></div>');
                group.attr('class', 'form-group')
                     .appendTo(form);

                $('<label></label>')
                    .text(plugin.options.strings.LABEL_FILTER_LIKE + ':')
                    .appendTo(group);

                $('<input></input>')
                    .attr('type', 'text')
                    .attr('class', 'form-control')
                    .attr('data-filter', 'like')
                    .appendTo(group);
            }

            if (props.filters.indexOf('equal') != -1) {
                if (props.type == 'boolean') {
                    var group = $('<div></div>');
                    group.attr('class', 'radio')
                         .appendTo(form);

                    var radio = $('<input type="radio">');
                    radio.attr('data-filter', 'equal-false')
                         .attr('name', plugin.id + '-' + id + '-equal')
                         .val(0);

                    var label = $('<label></label>');
                    label.html(radio)
                         .html(label.html() + plugin.options.strings.LABEL_FALSE)
                         .appendTo(group);

                    var group = $('<div></div>');
                    group.attr('class', 'radio')
                         .appendTo(form);

                    var radio = $('<input type="radio">');
                    radio.attr('data-filter', 'equal-true')
                         .attr('name', plugin.id + '-' + id + '-equal')
                         .val(1);

                    var label = $('<label></label>');
                    label.html(radio)
                         .html(label.html() + plugin.options.strings.LABEL_TRUE)
                        .appendTo(group);
                } else if (props.type == 'datetime') {
                    var group = $('<div></div>');
                    group.attr('class', 'form-group')
                         .appendTo(form);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_EQUAL + ':')
                        .appendTo(group);

                    var inputGroup = $('<div></div>');
                    inputGroup.attr('class', 'input-group date')
                              .appendTo(group);

                    var input = $('<input type="text">');
                    input.attr('class', 'form-control')
                         .attr('data-filter', 'equal')
                         .attr('data-date-format', plugin.options.strings.DATE_TIME_FORMAT)
                         .appendTo(inputGroup);

                    var span = $('<span></span>');
                    span.attr('class', 'input-group-btn')
                        .appendTo(inputGroup);

                    $('<button></button>')
                        .attr('class', 'btn btn-default')
                        .html('<span class="glyphicon glyphicon-calendar"></span>')
                        .appendTo(span);

                    var dtPicker = inputGroup.datetimepicker({
                        useSeconds: true,
                    });
                } else {
                    var group = $('<div></div>');
                    group.attr('class', 'form-group')
                         .appendTo(form);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_EQUAL + ':')
                        .appendTo(group);

                    $('<input></input>')
                        .attr('type', 'text')
                        .attr('class', 'form-control')
                        .attr('data-filter', 'equal')
                        .appendTo(group);
                }
            }

            if (props.filters.indexOf('between') != -1) {
                if (props.type == 'datetime') {
                    var group = $('<div></div>');
                    group.attr('class', 'form-group')
                         .appendTo(form);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_BETWEEN_START + ':')
                        .appendTo(group);

                    var inputGroup = $('<div></div>');
                    inputGroup.attr('class', 'input-group date')
                              .appendTo(group);

                    var input = $('<input type="text">');
                    input.attr('class', 'form-control')
                         .attr('data-filter', 'between-start')
                         .attr('data-date-format', plugin.options.strings.DATE_TIME_FORMAT)
                         .appendTo(inputGroup);

                    var span = $('<span></span>');
                    span.attr('class', 'input-group-btn')
                        .appendTo(inputGroup);

                    $('<button></button>')
                        .attr('class', 'btn btn-default')
                        .html('<span class="glyphicon glyphicon-calendar"></span>')
                        .appendTo(span);

                    var dtPicker1 = inputGroup.datetimepicker({
                        useSeconds: true,
                    });

                    var group = $('<div></div>');
                    group.attr('class', 'form-group')
                         .appendTo(form);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_BETWEEN_END + ':')
                        .appendTo(group);

                    var inputGroup = $('<div></div>');
                    inputGroup.attr('class', 'input-group date')
                              .appendTo(group);

                    var input = $('<input type="text">');
                    input.attr('class', 'form-control')
                         .attr('data-filter', 'between-end')
                         .attr('data-date-format', plugin.options.strings.DATE_TIME_FORMAT)
                         .appendTo(inputGroup);

                    var span = $('<span></span>');
                    span.attr('class', 'input-group-btn')
                        .appendTo(inputGroup);

                    $('<button></button>')
                        .attr('class', 'btn btn-default')
                        .html('<span class="glyphicon glyphicon-calendar"></span>')
                        .appendTo(span);

                    var dtPicker2 = inputGroup.datetimepicker({
                        useSeconds: true,
                    });
                } else {
                    var group = $('<div></div>');
                    group.attr('class', 'form-group')
                         .appendTo(form);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_BETWEEN_START + ':')
                        .appendTo(group);

                    $('<input></input>')
                        .attr('type', 'text')
                        .attr('class', 'form-control')
                        .attr('data-filter', 'between-start')
                        .appendTo(group);

                    $('<label></label>')
                        .text(plugin.options.strings.LABEL_FILTER_BETWEEN_END + ':')
                        .appendTo(group);

                    $('<input></input>')
                        .attr('type', 'text')
                        .attr('class', 'form-control')
                        .attr('data-filter', 'between-end')
                        .appendTo(group);
                }
            }

            if (props.filters.indexOf('null') != -1) {
                var group = $('<div></div>');
                group.attr('class', 'checkbox')
                     .appendTo(form);

                $('<label></label>')
                    .html(
                        '<input type="checkbox" data-filter="null">'
                        + plugin.options.strings.LABEL_FILTER_NULL
                    )
                    .appendTo(group);
            }

            $('<button></button>')
                .attr('type', 'submit')
                .attr('class', 'btn btn-primary')
                .text(plugin.options.strings.BUTTON_OK)
                .on('click', function () {
                    popover.css('display', 'none');
                    var data = {};
                    if (props.filters.indexOf('like') != -1) {
                        var like = form.find('input[data-filter=like]');
                        if (like.val().trim().length > 0)
                            data.like = like.val();
                    }
                    if (props.filters.indexOf('equal') != -1) {
                        if (props.type == 'boolean') {
                            var equalTrue = form.find('input[data-filter=equal-true]');
                            var equalFalse = form.find('input[data-filter=equal-false]');
                            if (equalTrue.prop('checked'))
                                data.equal = true;
                            else if (equalFalse.prop('checked'))
                                data.equal = false;
                        } else if (props.type == 'datetime') {
                            var equal = form.find('input[data-filter=equal]');
                            var value = dtPicker.data('DateTimePicker').getDate();
                            if (equal.val().trim().length > 0 && value != null)
                                data.equal = value.unix();
                        } else {
                            var equal = form.find('input[data-filter=equal]');
                            if (equal.val().trim().length > 0)
                                data.equal = equal.val();
                        }
                    }
                    if (props.filters.indexOf('between') != -1) {
                        if (props.type == 'datetime') {
                            var startInput = form.find('input[data-filter=between-start]');
                            var startValue = dtPicker1.data('DateTimePicker').getDate();
                            var endInput = form.find('input[data-filter=between-end]');
                            var endValue = dtPicker2.data('DateTimePicker').getDate();

                            var value1 = null;
                            if (startInput.val().trim().length > 0 && startValue != null)
                                value1 = startValue.unix();
                            var value2 = null;
                            if (endInput.val().trim().length > 0 && endValue != null)
                                value2 = endValue.unix();
                            if (value1 != null || value2 != null)
                                data.between = [ value1, value2 ];
                        } else {
                            var start = form.find('input[data-filter=between-start]');
                            var end = form.find('input[data-filter=between-end]');
                            start = start.val().trim().length > 0 ? start.val() : null;
                            end = end.val().trim().length > 0 ? end.val() : null;
                            if (start != null || end != null)
                                data.between = [ start, end ];
                        }
                    }
                    if (props.filters.indexOf('null') != -1) {
                        var check = form.find('input[data-filter=null]');
                        if (check.prop('checked'))
                            data.null = true;
                    }
                    plugin.setFilters(id, data);
                })
                .appendTo(form);

            $('<span>&nbsp;</span>').appendTo(form);

            $('<button></button>')
                .attr('class', 'btn btn-default')
                .text(plugin.options.strings.BUTTON_CLEAR)
                .on('click', function () {
                    popover.css('display', 'none');
                    plugin.setFilters(id, {});
                })
                .appendTo(form);

            $('<span>&nbsp;</span>').appendTo(form);

            $('<button></button>')
                .attr('class', 'btn btn-default')
                .text(plugin.options.strings.BUTTON_CANCEL)
                .on('click', function () {
                    popover.css('display', 'none');
                })
                .appendTo(form);
        });

        thead.appendTo(plugin.element.find('table'));
    };

    var _initTbody = function (plugin) {
        var tbodyEmpty = $('<tbody></tbody>');
        tbodyEmpty.attr('class', 'empty')
                  .attr('display', 'none');

        var tr = $('<tr></tr>')
        tr.appendTo(tbodyEmpty);

        var td = $('<td></td>')
        td.attr('colspan', plugin.visibleColumns)
          .appendTo(tr);

        var tbodyData = $('<tbody></tbody>');
        tbodyData.attr('class', 'data')
                 .attr('display', 'none')

        tbodyEmpty.appendTo(plugin.element.find('table'));
        tbodyData.appendTo(plugin.element.find('table'));
    };

    var _initTfoot = function (plugin) {
        var tfoot = $('<tfoot></tfoot>');

        var tr = $('<tr></tr>');
        tr.appendTo(tfoot);

        var td = $('<td></td>');
        td.attr('colspan', plugin.visibleColumns)
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

        $.each(plugin.options.page_sizes, function (index, value) {
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
                .html('<span class="glyphicon glyphicon-ok ' + (column.visible ? '' : 'invisible') + '"></span> ' + span.html())
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
            .html('<span class="glyphicon glyphicon-fast-backward"></span>')
            .on('click', function () {
                plugin.setPage(1);
            })
            .appendTo(group);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'previous')
            .html('<span class="glyphicon glyphicon-step-backward"></span>')
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
            .attr('class', 'input-group-addon pagination-before')
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
            .attr('class', 'input-group-addon pagination-after')
            .appendTo(inputGroup);

        var group = $('<div></div>');
        group.attr('class', 'btn-group')
             .attr('role', 'group')
             .appendTo(leftToolbar);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'next')
            .html('<span class="glyphicon glyphicon-step-forward"></span>')
            .on('click', function () {
                plugin.setPage(plugin.pageNumber + 1);
            })
            .appendTo(group);

        $('<button></button>')
            .attr('class', 'btn btn-default')
            .attr('data-action', 'last')
            .html('<span class="glyphicon glyphicon-fast-forward"></span>')
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

        tfoot.appendTo(plugin.element.find('table'));
    };

    var _enableColumnControls = function (plugin, id, enable)
    {
        var props = plugin.columns[id];

        plugin.element.find('.selector input')
                      .prop('disabled', !enable);

        var th = plugin.element.find('[data-column-id=' + id + ']');
        th.find('.text')
          .css('display', !enable || !props.sortable ? 'inline' : 'none');
        th.find('.link')
          .css('display', enable && props.sortable ? 'inline' : 'none');
        th.find('.sort-asc')
          .css(
              'display',
              enable && plugin.sortColumn == id && plugin.sortDir == 'asc'
                  ? 'inline' : 'none'
          );
        th.find('.sort-desc')
          .css(
              'display',
              enable && plugin.sortColumn == id && plugin.sortDir == 'desc'
                  ? 'inline' : 'none'
          );
        th.find('.filter')
          .css('display', enable && props.filters.length > 0 ? 'inline' : 'none');
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
        plugin.element.find('.pagination-input')
                      .val(plugin.pageNumber);

        plugin.element.find('tfoot .pagination-before')
            .text(plugin.options.strings.LABEL_PAGE_OF_1.replace('{0}', plugin.totalPages))

        plugin.element.find('tfoot .pagination-after')
            .text(plugin.options.strings.LABEL_PAGE_OF_2.replace('{0}', plugin.totalPages))

        plugin.element.find('thead button.filter')
                      .removeClass('btn-primary')
                      .addClass('btn-default');

        $.each(plugin.columns, function (id, props) {
            var th = plugin.element.find('thead th[data-column-id=' + id + ']');
            th.find('input[data-filter=like]').val('');
            th.find('input[data-filter=equal]').val('');
            th.find('input[data-filter=equal-true]').prop('checked', false);
            th.find('input[data-filter=equal-false]').prop('checked', false);
            th.find('input[data-filter=between-start]').val('');
            th.find('input[data-filter=between-end]').val('');
            th.find('input[data-filter=null]').prop('checked', false);

            if (typeof plugin.filters[id] == 'undefined')
                return;

            var hasFilter = false;

            var like = plugin.filters[id].like;
            if (typeof like != 'undefined') {
                th.find('input[data-filter=like]').val(like);
                hasFilter = true;
            }

            var equal = plugin.filters[id].equal;
            if (typeof equal != 'undefined') {
                if (props.type == 'boolean') {
                    if (equal)
                        th.find('input[data-filter=equal-true]').prop('checked', true);
                    else
                        th.find('input[data-filter=equal-false]').prop('checked', true);
                } else if (props.type == 'datetime') {
                    var dtPicker = th.find('input[data-filter=equal]').closest('.input-group');
                    dtPicker.data('DateTimePicker').setDate(moment(equal * 1000));
                } else {
                    th.find('input[data-filter=equal]').val(equal);
                }
                hasFilter = true;
            }

            var between = plugin.filters[id].between;
            if (typeof between != 'undefined') {
                if (props.type == 'datetime') {
                    if (between[0] != null) {
                        var dtPicker1 = th.find('input[data-filter=between-start]').closest('.input-group');
                        dtPicker1.data('DateTimePicker').setDate(moment(between[0] * 1000));
                    }
                    if (between[1] != null) {
                        var dtPicker2 = th.find('input[data-filter=between-end]').closest('.input-group');
                        dtPicker2.data('DateTimePicker').setDate(moment(between[1] * 1000));
                    }
                } else {
                    if (between[0] != null)
                        th.find('input[data-filter=between-start]').val(between[0]);
                    if (between[1] != null)
                        th.find('input[data-filter=between-end]').val(between[1]);
                }
                hasFilter = true;
            }

            var check = plugin.filters[id].null;
            if (typeof check != 'undefined') {
                th.find('input[data-filter=null]').prop('checked', true);
                hasFilter = true;
            }

            if (hasFilter) {
                th.find('button.filter')
                  .removeClass('btn-default')
                  .addClass('btn-primary');
            }
        });

        if (plugin.rows.length == 0) {
            plugin.element.find('.table-loader').remove();
            plugin.element.find('table').css('display', 'table');

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

            if (plugin.options.row_id_column != null) {
                var rowId = row[plugin.options.row_id_column];

                tr.attr('data-row-id', rowId);

                var selector = $('<td class="selector"><input type="checkbox"></td>');
                selector.appendTo(tr)
                        .find('input')
                        .prop('disabled', true)
                        .prop('value', rowId)
                        .on('click', function () {
                            var me = $(this);
                            if (me.prop('checked')) {
                                me.closest('tr').addClass('success');
                                plugin.element.trigger('dt.selected');
                            } else {
                                me.closest('tr').removeClass('success');
                                plugin.element.trigger('dt.deselected');
                            }

                            var all = plugin.element.find('tbody.data td.selector input:checked');
                            plugin.element.find('thead th.selector input')
                                          .prop('checked', all.length == plugin.rows.length);
                        });
            }

            var content = plugin.options.mapper != null
                ? plugin.options.mapper(row) : row;

            $.each(plugin.columns, function (id, props) {
                var td = $('<td></td>');
                td.html(content[id])
                  .attr('data-column-id', id)
                  .css('display', props.visible ? 'table-cell' : 'none')
                  .appendTo(tr)
            });

            tr.appendTo(tbodyData);
        });

        plugin.element.find('.table-loader').remove();
        plugin.element.find('table').css('display', 'table');

        plugin.element.find('tbody.empty').css('display', 'none');
        plugin.element.find('tbody.data').replaceWith(tbodyData)
                                         .css('display', 'table-row-group');
    };

}(jQuery, window, document));
