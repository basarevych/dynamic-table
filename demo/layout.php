<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Dynamic Table</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <link rel="stylesheet" href="vendor/bootstrap.min.css">
        <link rel="stylesheet" href="vendor/bootstrap-theme.min.css">
        <link rel="stylesheet" href="vendor/font-awesome.min.css">
        <link rel="stylesheet" href="vendor/bootstrap-datetimepicker.min.css">
        <link rel="stylesheet" href="jquery.dynamic-table.css">

        <script src="vendor/jquery.min.js"></script>
        <script src="vendor/moment-with-locales.min.js"></script>
        <script src="vendor/bootstrap.min.js"></script>
        <script src="vendor/bootstrap-datetimepicker.min.js"></script>
        <script src="jquery.dynamic-table.js"></script>
    </head>
    <body>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h3>Array-backed Dynamic Table</h3>
            <div id="table"></div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div id="selected"></div>
            <button class="btn btn-default" onclick="$('#selected').text('Selected: ' + table.getSelected().join(', '))">
                Get selected
            </button>
        </div>
    </div>
</div>

<script>
    var table = $('#table').dynamicTable({
        url: 'table.php',
        rowIdColumn: 'id',
        mapper: function (row) {
            if (row['string'] != null) {
                var span = $('<span></span');
                span.text(row['string']);
                row['string'] = span.text();
            }
            if (row['boolean'] != null) {
                row['boolean'] = '<i class="fa fa-'
                    + (row['boolean'] ? 'check text-success' : 'times text-danger')
                    + '"></i>';
            }
            if (row['datetime'] != null) {
                var m = moment(row['datetime'] * 1000);
                row['datetime'] = m.format('YYYY-MM-DD HH:mm:ss');
            }

            return row;
        },
    });
</script>


    </body>
</html>
