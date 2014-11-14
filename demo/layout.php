<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>ZF2 Skeleton</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="dynamic-table.css" media="screen" rel="stylesheet" type="text/css">
        <script src="moment.js"></script>
        <script src="moment-timezone-with-data.js"></script>
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
                    + (row['boolean'] ? 'check' : 'remove')
                    + '"></i>';
            }
            if (row['datetime'] != null) {
                var date = moment.tz(row['datetime'], 'X', 'UTC');
                row['datetime'] = date.local().format("YYYY-MM-DD H:mm:ss");
            }

            return row;
        },
    });
</script>


    </body>
</html>
