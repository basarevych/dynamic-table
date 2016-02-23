'use strict';

var app = angular.module('app', [ 'dynamicTable' ]);

app.controller('ctrl',
    ['$scope', 'dynamicTable',
    function($scope, dynamicTable) {
        $scope.table2Ctrl = dynamicTable({
            url: 'table.php',
            row_id_column: 'id',
            sort_column: 'id',
            mapper: function (row) {
                if (row['is_admin'] != null) {
                    row['is_admin'] = '<i class="glyphicon '
                        + (row['is_admin'] ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger')
                        + '"></i>';
                }
                if (row['created_at'] != null) {
                    var m = moment.unix(row['created_at']).local();         // we received date as Epoch timestamp
                    row['created_at'] = m.format('YYYY-MM-DD HH:mm:ss');    // convert it to a string in this browser timezone
                }

                return row;
            },
        });
        $scope.event2 = null;
        $scope.selected2 = null;

        $scope.getSelected = function () {
            var s = $scope.table2Ctrl.plugin.getSelected();
            if (s === 'all') {
                $scope.selected2 = 'All records on all the pages';
                return;
            }
            if (s.length)
                $scope.selected2 = 'Selected: ' + s.join(', ');
            else
                $scope.selected2 = 'Nothing selected';
        };

        $scope.$watch('table2Ctrl.event', function () {
            switch ($scope.table2Ctrl.event) {
                case 'loading': $scope.event2 = 'Loading'; break;
                case 'loaded': $scope.event2 = 'Loaded'; break;
                case 'selected': $scope.event2 = 'Selected'; break;
                case 'deselected': $scope.event2 = 'Deselected'; break;
                case 'http-error': $scope.event2 = 'HTTP Error: ' + $scope.table2Ctrl.statusCode; break;
            }
        });
    }]
);
