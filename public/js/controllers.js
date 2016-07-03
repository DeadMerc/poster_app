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
    )
    .controller('CategoryCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name:'category',url:'categories'};
        console.log($scope.params.name+" Ctrl init");
        $scope.data = {};
        $scope.init = function () {
            console.log($scope.params.name+" Ctrl scope init");
            $http.get('api/v1/'+$scope.params.url+'/s/edit', {ignoreLoadingBar: true})
                .then(function (res) {
                    console.log($scope.params.name+'  Ctrl loading schema');
                    //console.log(res.data.response);
                    $scope.schema = res.data.response;
                    //console.log($scope.schema);
                    angular.forEach(res.data.response, function (v, i) {
                        $scope.data[v.key];
                    })
                    //console.log($scope.user);
                });
            $scope.id = $routeParams.id;
            if (typeof $scope.id == 'undefined') {
                $scope.id = 'new';
            } else {
                $http.get('/api/v1/'+$scope.params.url+'/' + $scope.id).then(function (res) {
                    $scope.data = res.data.response;
                    //console.log($scope.user);
                });
            }
            console.log($scope.params.name+' Ctrl try edit id ' + $scope.id);
        };
        /*upload image*/
        $scope.upload = function (file) {
            console.log($scope.params.name+' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name+' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name+' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if(res.status == 200){
                            $scope.data.image = res.data;
                            $scope.uploadedFile = res.data;
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name+' Ctrl try save user with id:' + $scope.id);
            if ($scope.id !== 'new') {
                //console.log($scope.user);
                //$rootScope.transform(
                $http.put('/api/v1/'+$scope.params.url+'/' + $scope.id, $rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            //$location.path('/users');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }else{
                //console.log($scope.user);
                console.log($scope.params.name+' Ctrl try save new row');
                $http.post('/api/v1/'+$scope.params.url+'',$rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            //$location.path('/users');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }

        }
    })
    .controller('UsersCtrl', function ($rootScope, $scope, $http, $location, $mdDialog) {
        console.log("Users Ctrl init");
        $scope.params = {name:'Users',url:'users'};
        $scope.init = function () {
            console.log($scope.params.name+" Ctrl scope init");
            $http.get("/api/v1/"+$scope.params.url+"")
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log($scope.params.name+" Ctrl data to view");
                        $scope.users = res.data.response;
                    } else {
                        console.log($scope.params.name+" Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log($scope.params.name+" Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };
        $scope.ban = function (user) {
            var id = user.id;
            console.log($scope.params.name+" Ctrl try ban user with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want ban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/'+$scope.params.url+'/ban/' + id, $rootScope.config)
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
            console.log($scope.params.name+" Ctrl try unban user with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want unban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/'+$scope.params.url+'/unban/' + id, $rootScope.config)
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
            console.log($scope.params.name+' Ctrl try edit ' + id);
            $location.path("/user/" + id);
        };
        $scope.delete = function (id) {
            console.log($scope.params.name+' Ctrl try delete ' + id);
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
    .controller('UserCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name:'User',url:'users'};
        console.log($scope.params.name+" Ctrl init");
        $scope.data = {};
        $scope.init = function () {
            console.log($scope.params.name+" Ctrl scope init");
            $http.get('api/v1/'+$scope.params.url+'/s/edit', {ignoreLoadingBar: true})
                .then(function (res) {
                    console.log($scope.params.name+'  Ctrl loading schema');
                    //console.log(res.data.response);
                    $scope.schema = res.data.response;
                    //console.log($scope.schema);
                    angular.forEach(res.data.response, function (v, i) {
                        $scope.data[v.key];
                    })
                    //console.log($scope.data);
                });
            $scope.id = $routeParams.id;
            if (typeof $scope.id == 'undefined') {
                $scope.id = 'new';
            } else {
                $http.get('/api/v1/'+$scope.params.url+'/' + $scope.id).then(function (res) {
                    $scope.data = res.data.response;
                    console.log($scope.data);
                });
            }
            console.log($scope.params.name+' Ctrl try edit id ' + $scope.id);
        };
        /*upload image*/
        $scope.upload = function (file) {
            console.log($scope.params.name+' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name+' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name+' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if(res.status == 200){
                            $scope.data.image = res.data;
                            $scope.uploadedFile = res.data;
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name+' Ctrl try save user with id:' + $scope.id);
            if ($scope.id !== 'new') {
                //console.log($scope.data);
                //$rootScope.transform(
                $http.put('/api/v1/'+$scope.params.url+'/' + $scope.id, $rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            $location.path('/users');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }else{
                //console.log($scope.data);
                console.log($scope.params.name+' Ctrl try save new row');
                $http.post('/api/v1/'+$scope.params.url+'',$rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            $location.path('/users');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }

        }
    })
    .controller('EventsCtrl', function ($rootScope, $scope, $http, $location, $mdDialog) {
        $scope.params = {name:'Events',url:'events',editurl:'event'};
        console.log($scope.params.name+" Ctrl init");
        $scope.init = function () {
            console.log($scope.params.name+" Ctrl scope init");
            $http.get("/api/v1/"+$scope.params.url+"")
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log($scope.params.name+" Ctrl data to view");
                        $scope.users = res.data.response;
                    } else {
                        console.log($scope.params.name+" Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log($scope.params.name+" Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };
        $scope.ban = function (user) {
            var id = user.id;
            console.log($scope.params.name+" Ctrl try ban user with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want ban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/'+$scope.params.url+'/ban/' + id, $rootScope.config)
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
            console.log($scope.params.name+' Ctrl try unban user with id' + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want unban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/'+$scope.params.url+'/unban/' + id, $rootScope.config)
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
            console.log($scope.params.name+' Ctrl try edit ' + id);
            $location.path("/"+$scope.params.editurl+"/" + id);
        };
        $scope.delete = function (id) {
            console.log($scope.params.name+' Ctrl try delete ' + id);
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
    .controller('EventCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name:'Event',url:'events'};
        console.log($scope.params.name+" Ctrl init");
        $scope.data = {};
        $scope.init = function () {
            console.log($scope.params.name+" Ctrl scope init");
            $http.get('api/v1/'+$scope.params.url+'/s/edit', {ignoreLoadingBar: true})
                .then(function (res) {
                    console.log($scope.params.name+'  Ctrl loading schema');
                    //console.log(res.data.response);
                    $scope.schema = res.data.response;
                    //console.log($scope.schema);
                    angular.forEach(res.data.response, function (v, i) {
                        $scope.data[v.key];
                    })
                    //console.log($scope.data);
                });
            $scope.id = $routeParams.id;
            if (typeof $scope.id == 'undefined') {
                $scope.id = 'new';
            } else {
                $http.get('/api/v1/'+$scope.params.url+'/' + $scope.id).then(function (res) {
                    $scope.data = res.data.response;
                    console.log($scope.data);
                });
            }
            console.log($scope.params.name+' Ctrl try edit id ' + $scope.id);
        };
        /*upload image*/
        $scope.upload = function (file) {
            console.log($scope.params.name+' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name+' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name+' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if(res.status == 200){
                            $scope.data.image = res.data;
                            $scope.uploadedFile = res.data;
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name+' Ctrl try save user with id:' + $scope.id);
            if ($scope.id !== 'new') {
                //console.log($scope.data);
                //$rootScope.transform(
                $http.put('/api/v1/'+$scope.params.url+'/' + $scope.id, $rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            $location.path('/'+$scope.params.url+'');
                        } else {

                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }else{
                                msg = res.data.message;
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }else{
                //console.log($scope.data);
                console.log($scope.params.name+' Ctrl try save new row');
                $http.post('/api/v1/'+$scope.params.url+'',$rootScope.transform($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            $location.path('/'+$scope.params.url+'');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            }else{
                                msg = res.data.message;
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }

        }
    });