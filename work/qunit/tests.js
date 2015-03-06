module(
    "Tests",
    {
        setup: function (assert) {
            jQuery.getJSON = function (url, data, callback) {
                if (data['query'] === 'describe') {
                    callback($description);
                } else if (data['query'] === 'data') {
                    switch ($showData) {
                        case 'first':
                            callback($dataFirstPage);
                            break;
                        case 'middle':
                            callback($dataMiddlePage);
                            break;
                        case 'last':
                            callback($dataLastPage);
                            break;
                        default:
                            assert.ok(false, 'Which data page?');
                    }
                } else {
                    assert.ok(false, 'Unknown query');
                }
            };

            var fixture = $('#qunit-fixture');
            fixture.append('<div id="table"></div>');
        },
        teardown: function (assert) {
            $showData = 'middle';
        }
    }
);
 
test("init()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl', row_id_column: 'id' });

    assert.ok(root.find('table').length, "table created");
    assert.ok(root.find('table thead').length, "thead created");
    assert.ok(root.find('table tbody.empty').length, "tbody.empty created");
    assert.ok(root.find('table tbody.data').length, "tbody.data created");
    assert.ok(root.find('table tfoot').length, "tfoot created");

    $.each(table.columns, function (id, props) {
        var th = root.find('thead th[data-column-id=' + id + ']');
        assert.ok(th.length, "Column <th> created: " + id);

        var label = th.find('.text').text();
        assert.equal(label, props.title, "Correct text label for " + id);

        var label = th.find('.link').text();
        assert.equal(label, props.title, "Correct link label for " + id);

        var td = root.find('tbody.data td[data-column-id=' + id + ']');
        assert.ok(td.length, "Column <td> created: " + id);
    });
});

test("enable()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    $.each([ true, false ], function (index, enabled) {
        table.enable(enabled);

        root.find('thead th').each(function (index, element) {
            var id = $(element).attr('data-column-id');

            if (id == 'id') {
                assert.equal(
                    $(element).find('.text').css('display'),
                    'inline',
                    "Text label visibility: " + id
                );
                assert.equal(
                    $(element).find('.link').css('display'),
                    'none',
                    "Link label visibility: " + id
                );
                assert.equal(
                    $(element).find('.sort-asc').css('display'),
                    'none',
                    "Sort ASC visibility: " + id
                );
                assert.equal(
                    $(element).find('.sort-desc').css('display'),
                    'none',
                    "Sort DESC visibility: " + id
                );
                assert.equal(
                    $(element).find('.filter').length,
                    0,
                    "Filter visibility: " + id
                );
            } else {
                assert.equal(
                    $(element).find('.text').css('display'),
                    enabled ? 'none' : 'inline',
                    "Text label visibility: " + id
                );
                assert.equal(
                    $(element).find('.link').css('display'),
                    enabled ? 'inline' : 'none',
                    "Link label visibility: " + id
                );
                assert.equal(
                    $(element).find('.sort-asc').css('display'),
                    enabled && id == 'string' ? 'inline' : 'none',
                    "Sort ASC visibility: " + id
                );
                assert.equal(
                    $(element).find('.sort-desc').css('display'),
                    'none',
                    "Sort DESC visibility: " + id
                );
                assert.equal(
                    $(element).find('.filter').css('display'),
                    enabled ? 'inline-block' : 'none',
                    "Filter visibility: " + id
                );
            }
        });

        assert.equal(
            root.find('.overlay-back').css('display'),
            enabled ? 'none' : 'block',
            "Overlay background visibility"
        );
        assert.equal(
            root.find('.overlay-loader').css('display'),
            enabled ? 'none' : 'block',
            "Overlay loader visibility"
        );

        $.each([ 'first', 'middle', 'last' ], function (index, page) {
            $showData = page;
            table.refresh();
            table.enable(enabled);

            assert.equal(
                root.find('tfoot button[data-action=first]').prop('disabled'),
                page == 'first' || !enabled,
                "Move to first page clickable"
            );
            assert.equal(
                root.find('tfoot button[data-action=previous]').prop('disabled'),
                page == 'first' || !enabled,
                "Move to previous page clickable"
            );
            assert.equal(
                root.find('tfoot button[data-action=next]').prop('disabled'),
                page == 'last' || !enabled,
                "Move to next page clickable"
            );
            assert.equal(
                root.find('tfoot button[data-action=last]').prop('disabled'),
                page == 'last' || !enabled,
                "Move to last page clickable"
            );
            assert.equal(
                root.find('tfoot button[data-action=refresh]').prop('disabled'),
                !enabled,
                "Refresh page clickable"
            );
            assert.equal(
                root.find('tfoot .pagination-input').prop('disabled'),
                !enabled,
                "Page number input clickable"
            );
        });
    });
});

test("setSize()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    var actual = null;
    table.refresh = function (params) {
        actual = params;
    };

    table.setSize(10);
    assert.equal(actual['page_size'], 10, "refresh() is called correctly");
});

test("setPage()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    var actual = null;
    table.refresh = function (params) {
        actual = params;
    };

    table.setPage(-10);
    assert.equal(actual['page_number'], 1, "refresh() is called correctly when page < 1 requested");

    table.setPage(10);
    assert.equal(actual['page_number'], 3, "refresh() is called correctly when page < 1 requested");

    table.setPage(2);
    assert.equal(actual['page_number'], 2, "refresh() is called correctly when page is valid");
});

test("setFilters()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    var actual = null;
    table.refresh = function (params) {
        actual = params;
    };

    table.setFilters('id', { 'foo': 'bar' });
    assert.equal(actual['filters']['id']['foo'], 'bar', "refresh() is called correctly");
});

test("toggleSort()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    var actual = null;
    table.refresh = function (params) {
        actual = params;
    };

    table.toggleSort('id');
    assert.equal(actual['sort_column'], 'id', "refresh() is called correctly (sort_column)");
    assert.equal(actual['sort_dir'], 'asc', "refresh() is called correctly (sort_dir)");
});

test("toggleColumn()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    assert.equal(table.columns['string'].visible, true, "Column initially visible");

    table.toggleColumn('string');
    assert.equal(table.columns['string'].visible, false, "Column switched off");

    table.toggleColumn('string');
    assert.equal(table.columns['string'].visible, true, "Column switched back on");
});

test("toggleSelected()/getSelected()", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl', row_id_column: 'id' });

    var input = root.find('tbody.data tr[data-row-id=2] td.selector input');
    var selected = table.getSelected();
    assert.equal(input.prop('checked'), false, "Initially not selected");
    assert.equal(selected.length, 0, "Empty array as selected");

    table.toggleSelected(2);
    var input = root.find('tbody.data tr[data-row-id=2] td.selector input');
    var selected = table.getSelected();
    assert.equal(input.prop('checked'), true, "Row selected now");
    assert.equal(selected.length, 1, "One row listed in selected array");
    assert.equal(selected[0], "2", "Correct row returned in selected array");

    table.toggleSelected(2);
    var input = root.find('tbody.data tr[data-row-id=2] td.selector input');
    var selected = table.getSelected();
    assert.equal(input.prop('checked'), false, "Switched off again");
    assert.equal(selected.length, 0, "Empty array again");

    table.toggleSelected(1);
    table.toggleSelected(2);
    table.toggleSelected(3);
    table.toggleSelected(4);
    table.toggleSelected(5);
    root.find('thead .all-selector-menu input').val(['all']);
    var selected = table.getSelected();
    assert.equal(selected, 'all', "All records selected");
});

test("Table filters", function (assert) {
    var root = $('#table');
    var table = root.dynamicTable({ url: 'someurl' });

    $.each(table.columns, function (id, props) {
        if (props.filters.length == 0)
            return;

        var th = root.find('thead th[data-column-id=' + id + ']');
        var button = th.find('button.filter');
        var popover = th.find('.popover');

        button.trigger('click');
        assert.notEqual(popover.css('display'), 'none', "Popover filter form");

        var expected = {};
        $.each(props.filters, function (index, filter) {
            switch (filter) {
                case 'like':
                    var input = popover.find('input[data-filter=like]');
                    input.val('test');
                    expected.like = 'test';
                    break;
                case 'equal':
                    if (props.type == 'boolean') {
                        var input = popover.find('input[data-filter=equal-true]');
                        input.prop('checked', true);
                        expected.equal = true;
                    } else if (props.type == 'datetime') {
                        var input = popover.find('input[data-filter=equal]');
                        input.val('2010-05-01').trigger('change');
                        var value = moment(input.val()).unix();
                        expected.equal = value;
                    } else {
                        var input = popover.find('input[data-filter=equal]');
                        input.val('test');
                        expected.equal = 'test';
                    }
                    break;
                case 'between':
                    if (props.type == 'datetime') {
                        var input1 = popover.find('input[data-filter=between-start]');
                        input1.val('2010-05-01').trigger('change');
                        var value1 = moment(input1.val()).unix();
                        var input2 = popover.find('input[data-filter=between-end]');
                        input2.val('2012-05-01').trigger('change');
                        var value2 = moment(input2.val()).unix();
                        expected.between = [ value1, value2 ];
                    } else {
                        var input1 = popover.find('input[data-filter=between-start]');
                        input1.val('test1');
                        var input2 = popover.find('input[data-filter=between-end]');
                        input2.val('test2');
                        expected.between = [ 'test1', 'test2' ];
                    }
                    break;
            }
        });

        var actualId = null;
        var actualParams = null;
        table.setFilters = function (id, params) {
            actualId = id;
            actualParams = params;
        };

        popover.find('button[type=submit]').trigger('click');

        assert.equal(actualId, id, "Correct field ID: " + id);
        assert.deepEqual(actualParams, expected, "Correct filter params: " + id);
    });
});
