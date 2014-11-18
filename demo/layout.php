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
        <link rel="stylesheet" href="dynamic-table.css">

        <script src="vendor/jquery.min.js"></script>
        <script src="vendor/bootstrap.min.js"></script>
        <script src="jquery.dynamic-table.js"></script>
    </head>
    <body>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h3>Array-backed Dynamic Table</h3>
            <div id="table"></div>
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
                var date = new Date(row['datetime'] * 1000);
                row['datetime'] = date.getFullYear() + '-'
                    + ('0' + (date.getMonth()+1)).slice(-2) + '-'
                    + ('0' + date.getDate()).slice(-2) + ' '
                    + ('0' + date.getHours()).slice(-2) + ':'
                    + ('0' + date.getMinutes()).slice(-2) + ':'
                    + ('0' + date.getSeconds()).slice(-2);
            }

            return row;
        },
    });
</script>


    </body>
</html>
