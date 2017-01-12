app.controller('tableCtrl', function ($scope, $rootScope, $routeParams, $location, Data, AUTH_EVENTS) {
    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        Data.get('tables').then(function (results) {
            Data.toast(results);
            $scope.tables = results.tables;
            console.log($scope.tables);
        });
    });

    $scope.tableUpdate = function (tableNum) {
        Data.put('tables/' + tableNum, {
            //nothing needed for this call
        }).then(function (results) {
            Data.toast(results);
            Data.get('tables').then(function (results) {
                $scope.tables = results.tables;
            });
        });
    };

    $scope.removeFromTables = function () {
        Data.delete('tables').then(function (results) {
            Data.toast(results);
            Data.get('tables').then(function (results) {
                $scope.tables = results.tables;
            });
        });
    };
});
