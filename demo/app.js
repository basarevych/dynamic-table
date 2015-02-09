'use strict';

var app = angular.module('app', [ 'dynamicTable' ]);

app.controller('ctrl',
    ['$scope', 'dynamicTable',
    function($scope, dynamicTable) {
        $scope.table2Ctrl = dynamicTable({
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
        $scope.event2 = null;
        $scope.selected2 = null;

        $scope.getSelected = function () {
            $scope.selected2 = 'Selected: ' + $scope.table2Ctrl.plugin.getSelected().join(', ');
        };

        $scope.$watch('table2Ctrl.event', function () {
            switch ($scope.table2Ctrl.event) {
                case 'loading': $scope.event2 = 'Loading'; break;
                case 'loaded': $scope.event2 = 'Loaded'; break;
                case 'selected': $scope.event2 = 'Selected'; break;
                case 'deselected': $scope.event2 = 'Deselected'; break;
            }
        });
    }]
);
