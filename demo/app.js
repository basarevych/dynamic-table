'use strict';

var app = angular.module('app', [ 'dynamicTable' ]);

app.controller('ctrl',
    ['$scope', 'dynamicTable',
    function($scope, dynamicTable) {
        $scope.table1Ctrl = dynamicTable({
            url: 'table.php',
            row_id_column: 'id',
            mapper: function (row) {
                if (row['boolean'] != null) {
                    row['boolean'] = '<i class="glyphicon '
                        + (row['boolean'] ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger')
                        + '"></i>';
                }
                if (row['datetime'] != null) {
                    var m = moment(row['datetime'] * 1000);
                    row['datetime'] = m.format('YYYY-MM-DD HH:mm:ss');
                }

                return row;
            },
        });
    }]
);


