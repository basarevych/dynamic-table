<!DOCTYPE html>
<html lang="en" ng-app="app">
    <head>
        <meta charset="utf-8">
        <title>Dynamic Table</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <link rel="stylesheet" href="vendor/bootstrap.min.css">
        <link rel="stylesheet" href="vendor/bootstrap-theme.min.css">
        <link rel="stylesheet" href="vendor/bootstrap-datetimepicker.min.css">
        <link rel="stylesheet" href="jquery.dynamic-table.css">

        <script src="vendor/jquery.min.js"></script>
        <script src="vendor/moment-with-locales.min.js"></script>
        <script src="vendor/bootstrap.min.js"></script>
        <script src="vendor/bootstrap-datetimepicker.min.js"></script>
        <script src="vendor/angular.min.js"></script>

        <script src="jquery.dynamic-table.js"></script>
        <script src="angularjs.dynamic-table.js"></script>
        <script src="app.js"></script>
    </head>
    <body ng-controller="ctrl">

<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <button class="btn btn-default" onclick="getSelected()">
                Get selected
            </button>
            <pre id="selected1">&nbsp;</pre>
        </div>
        <div class="col-lg-6">
            <div>Last Event:</div>
            <pre id="event1">&nbsp;</pre>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <h3>Plain jQuery Dynamic Table</h3>
            <div id="table"></div>
        </div>
    </div>
    <div class="row"><hr></div>
    <div class="row">
        <div class="col-lg-6">
            <button class="btn btn-default" ng-click="getSelected()">
                Get selected
            </button>
            <pre ng-bind="selected2">&nbsp;</pre>
        </div>
        <div class="col-lg-6">
            <div>Last Event:</div>
            <pre ng-bind="event2">&nbsp;</pre>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <h3>AngularJS Dynamic Table</h3>
            <div id="table2" dynamic-table="table2Ctrl"></div>
        </div>
    </div>
</div>

<script>
    var table = $('#table').dynamicTable({
        url: 'table.php',
        locale: 'ru',
        row_id_column: 'id',
        sort_column: 'id',
        mapper: function (row) {
            if (row['is_admin'] != null) {
                row['is_admin'] = '<i class="glyphicon '
                    + (row['is_admin'] ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger')
                    + '"></i>';
            }
            if (row['created_at'] != null) {
                var m = moment.unix(row['created_at']).local();
                row['created_at'] = m.format('YYYY-MM-DD HH:mm:ss');
            }

            return row;
        },
    });

    $('#table').on('dt.loading', function (e) {
        $('#event1').text('Loading');
    });
    $('#table').on('dt.loaded', function (e) {
        $('#event1').text('Loaded');
    });
    $('#table').on('dt.selected', function (e) {
        $('#event1').text('Selected');
    });
    $('#table').on('dt.deselected', function (e) {
        $('#event1').text('Deselected');
    });

    function getSelected() {
        var s = table.getSelected();
        if (s === 'all') {
            $('#selected1').text('All records on all the pages');
            return;
        }
        if (s.length)
            $('#selected1').text('Selected: ' + s.join(', '));
        else
            $('#selected1').text('Nothing selected');
    }
</script>


    </body>
</html>
