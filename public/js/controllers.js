/**
 * Created by dead on 15.05.2016.
 */
var adminControllers = angular.module('adminControllers', [])
    .controller('CategoriesCtrl', function ($rootScope, $scope, $http, $location, $mdDialog) {
            console.log("Categories Ctrl init");
            $scope.init = function () {
                console.log("Categories Ctrl scope init");
                $http.get("/api/v1/categories")
                    .then(function (res) {
                        if (res.data.error == false) {
                            console.log("Categories Ctrl data to view");
                            $scope.categories = res.data.response;
                        } else {
                            console.log("Categories Ctrl data have error");
                            $rootScope.warning('Request return error');
                        }
                    }, function (res) {
                        console.log("Categories Ctrl bad request");
                        $rootScope.error('Request failed');
                    });
            };

            $scope.edit = function (id) {
                console.log('Categories Ctrl try edit ' + id);
                $location.path('/category/' + id);
            };

            $scope.delete = function (id) {
                console.log('Categories Ctrl try delete ' + id);
                // Appending dialog to document.body to cover sidenav in docs app
                var confirm = $mdDialog.confirm()
                    .textContent('Your want delete item with id:' + id + '?')
                    .ok('Please do it!')
                    .cancel('No');
                $mdDialog.show(confirm).then(function () {
                    console.log('first');
                }, function () {
                    console.log('second')
                });

            };


        }
    ).controller('CategoryCtrl', function ($rootScope, $scope, $http, $routeParams, $location) {
        console.log("Category Ctrl init");
        $scope.init = function () {
            console.log("Category Ctrl scope init");
            $scope.id = $routeParams.id;
            $scope.info = $scope.id;
            if (typeof $scope.id == 'undefined') {
                $scope.info = 'new Category';
            }
            console.log('Category Ctrl try edit id ' + $scope.info);
        };
    })
    .controller('UsersCtrl', function ($rootScope, $scope, $http, $location, $mdDialog) {
        console.log("Users Ctrl init");
        $scope.init = function () {
            console.log("Users Ctrl scope init");
            $http.get("/api/v1/users")
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log("Users Ctrl data to view");
                        $scope.users = res.data.response;
                    } else {
                        console.log("Users Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log("Users Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };

        $scope.ban = function (user) {
            var id = user.id;
            console.log('Users Ctrl try ban user with id' + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want ban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/users/ban/' + id, $rootScope.config)
                    .then(function (res) {

                        if (res.data.error !== true) {
                            user.banned = 1;
                            $rootScope.success('Request is done');
                        } else if (res.data.error == true) {
                            console.log(res);
                            $rootScope.warning('Request return error try with:' + res.data.response + '<br>' + res.data.message);
                        }
                    }, function (res) {
                        console.log(res);
                        $rootScope.error('Request return error code');
                    });
            }, function () {
                $rootScope.info('Banned user was aborted');
            });

        };
        $scope.unban = function (user) {
            var id = user.id;
            console.log('Users Ctrl try unban user with id' + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want unban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/users/unban/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            user.banned = 0;
                            $rootScope.success('Request is done');
                        } else if (res.data.error == true) {
                            console.log(res);
                            $rootScope.warning('Request return error try with:' + res.data.response + '<br>' + res.data.message);
                        }
                    }, function (res) {
                        console.log(res);
                        $rootScope.error('Request return error code');
                    });
            }, function () {
                $rootScope.info('UnBanned user was aborted');
            });
        };

        $scope.edit = function (id) {
            console.log('Users Ctrl try edit ' + id);
            $location.path("/user/" + id);
        };

        $scope.delete = function (id) {
            console.log('Users Ctrl try delete ' + id);
            var confirm = $mdDialog.confirm()
                .title('Really?')
                .textContent('Your want delete item with id:' + id + '?')
                .ariaLabel('Lucky day')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                console.log('first');
            }, function () {
                console.log('second')
            });
        };
    })
    .controller('UserCtrl', function ($rootScope, $scope, $http, $routeParams, $location) {
        console.log("User Ctrl init");
        $scope.init = function () {
            console.log("User Ctrl scope init");
            $http.get('api/v1/users/s/edit',{ignoreLoadingBar: true})
                .then(function (res) {
                    console.log('User Ctrl loading schema');
                    //console.log(res.data.response);
                    $scope.schema = res.data.response;
                });
            $scope.id = $routeParams.id;
            if (typeof $scope.id == 'undefined') {
                $scope.id = 'new';
            }else{
                $http.get('/api/v1/users/'+$scope.id).then(function (res) {
                    $scope.user = res.data.response;
                    //console.log($scope.user);
                });
            }
            console.log('User Ctrl try edit id '+ $scope.id);
        };

        $scope.save = function (){
            console.log('User Ctrl try save user with id:'+$scope.id);
            if($scope.id !== 'new'){
                $http.put('/api/v1/users/'+$scope.id,$scope.user,$rootScope.config)
                    .then(function (res) {
                        if(res.data.error == false){
                            $rootScope.success('OK');
                            $location.path('/users');
                        }else{
                            if(res.data.message == 'valid'){
                                var msg = '';
                                angular.forEach(res.data.validator,function (v,i) {
                                    msg += v+'<br>';
                                })
                            }
                            $rootScope.warning(msg);
                        }
                    },function (res) {
                        $rootScope.error('Request return failed');
                    });
            }

        }

        $scope.upload = function () {
            angular.element(document.querySelector('#fileInput')).click();
        };
    });