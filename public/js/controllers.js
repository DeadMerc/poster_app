/**
 * Created by dead on 15.05.2016.
 */
var adminControllers = angular.module('adminControllers', ['uiGmapgoogle-maps'])
    .controller('CategoriesCtrl', function ($rootScope, $scope, $http, $location, $mdDialog) {
        console.log("Categories Ctrl init");
        $scope.params = {name: 'category', url: 'categories'};
        $scope.init = function () {
            console.log("Categories Ctrl scope init");
            $http.get("/api/v1/categories", {
                headers: {
                    'token': 'adm',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'lang': "RU"
                }
            })
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log("Categories Ctrl data to view");
                        console.log(res.data);
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
            console.log($scope.params.name + ' Ctrl try delete ' + id);
            var confirm = $mdDialog.confirm()
                .title('Really?')
                .textContent('Your want delete item with id:' + id + '?')
                .ariaLabel('Lucky day')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.delete('/api/v1/' + $scope.params.url + '/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            $rootScope.success('Request is done');
                            $scope.init();
                        } else if (res.data.error == true) {
                            console.log(res);
                            $rootScope.warning('Request return error try with:' + res.data.response + '<br>' + res.data.message);
                        }
                    }, function (res) {
                        console.log(res);
                        $rootScope.error('Request return error code');
                    });
            }, function () {
                $rootScope.info('Request was aborted');
            });
        };
    })
    .controller('CategoryCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name: 'category', url: 'categories'};
        console.log($scope.params.name + " Ctrl init");
        $scope.data = {};
        $scope.init = function () {
            console.log($scope.params.name + " Ctrl scope init");
            $http.get('api/v1/' + $scope.params.url + '/s/edit', {ignoreLoadingBar: true})
                .then(function (res) {
                    console.log($scope.params.name + '  Ctrl loading schema');
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
                $http.get('/api/v1/category/' + $scope.id).then(function (res) {
                    $scope.data = res.data.response;
                    //console.log($scope.user);
                });
            }
            console.log($scope.params.name + ' Ctrl try edit id ' + $scope.id);
        };
        /*upload image*/
        $scope.upload = function (file) {
            console.log($scope.params.name + ' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name + ' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name + ' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if (res.status == 200) {
                            $scope.data.image = res.data;
                            $scope.uploadedFile = res.data;
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name + ' Ctrl try save category with id:' + $scope.id);
            if ($scope.id !== 'new') {
                console.log($scope.data);
                //$rootScope.transform(
                $http.put('/api/v1/' + $scope.params.url + '/' + $scope.id, $rootScope.transform($scope.data), $rootScope.config)
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
            } else {
                //console.log($scope.user);
                console.log($scope.params.name + ' Ctrl try save new row');
                $http.post('/api/v1/' + $scope.params.url + '', $rootScope.transform($scope.data), $rootScope.config)
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
    .controller('UsersCtrl', function ($rootScope, $scope, $http, $location, $mdDialog, $routeParams) {
        console.log("Users Ctrl init");
        $scope.params = {name: 'Users', url: 'users'};
        $scope.sortType = 'id'; // set the default sort type
        $scope.sortReverse = false;  // set the default sort order
        $scope.search = $routeParams.search;

        $scope.paging = {
            total: 1,
            current: 1,
            onPageChanged: function () {
                $scope.init();
            }
        };

        $scope.init = function () {
            console.log($scope.params.name + " Ctrl scope init");
            if (typeof $scope.search == 'undefined') {
                $scope.search = '';
            }
            $http.get("/api/v1/" + $scope.params.url + "?page=" + $scope.paging.current + "&search=" + $scope.search)
                .then(function (res) {
                    $scope.paging.total = res.data.response.last_page;
                    $scope.paging.current = res.data.response.current_page;
                    if (res.data.error == false) {
                        console.log($scope.params.name + " Ctrl data to view");
                        $scope.users = res.data.response.data;
                    } else {
                        console.log($scope.params.name + " Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log($scope.params.name + " Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };
        $scope.ban = function (user) {
            var id = user.id;
            console.log($scope.params.name + " Ctrl try ban user with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want ban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/' + $scope.params.url + '/ban/' + id, $rootScope.config)
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
            console.log($scope.params.name + " Ctrl try unban user with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want unban user with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/' + $scope.params.url + '/unban/' + id, $rootScope.config)
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
            console.log($scope.params.name + ' Ctrl try edit ' + id);
            $location.path("/user/" + id);
        };
        $scope.events = function (email) {
            console.log($scope.params.name + ' Ctrl try show events ' + email);
            $location.url("/events?search=" + email);
        };
        $scope.delete = function (id) {
            console.log($scope.params.name + ' Ctrl try delete ' + id);
            var confirm = $mdDialog.confirm()
                .title('Really?')
                .textContent('Your want delete item with id:' + id + '?')
                .ariaLabel('Lucky day')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.delete('/api/v1/' + $scope.params.url + '/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            $rootScope.success('Request is done');
                            $scope.init();
                        } else if (res.data.error == true) {
                            console.log(res);
                            $rootScope.warning('Request return error try with:' + res.data.response + '<br>' + res.data.message);
                        }
                    }, function (res) {
                        console.log(res);
                        $rootScope.error('Request return error code');
                    });
            }, function () {
                console.log('second')
            });
        };
    })
    .controller('UserCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name: 'User', url: 'users'};
        console.log($scope.params.name + " Ctrl init");
        $scope.data = {};
        $scope.init = function () {
            console.log($scope.params.name + " Ctrl scope init");
            $http.get('api/v1/' + $scope.params.url + '/s/edit', {ignoreLoadingBar: true})
                .then(function (res) {
                    console.log($scope.params.name + '  Ctrl loading schema');
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
                $http.get('/api/v1/' + $scope.params.url + '/' + $scope.id).then(function (res) {
                    $scope.data = res.data.response;
                    //console.log($scope.data);
                });
            }
            console.log($scope.params.name + ' Ctrl try edit id ' + $scope.id);
        };
        /*upload image*/
        $scope.upload = function (file) {
            console.log($scope.params.name + ' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name + ' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name + ' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if (res.status == 200) {
                            $scope.data.image = res.data;
                            $scope.uploadedFile = res.data;
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name + ' Ctrl try save user with id:' + $scope.id);
            if ($scope.id !== 'new') {
                //console.log($scope.data);
                //$rootScope.transform(
                console.log($scope.data);
                //var paramSerializer = $httpParamSerializerProvider.$get();
                $http.post('/api/v1/' + $scope.params.url + '/' + $scope.id, $rootScope.serialize($scope.data), $rootScope.config)
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
            } else {
                //console.log($scope.data);
                console.log($scope.params.name + ' Ctrl try save new row');
                $http.post('/api/v1/' + $scope.params.url + '', $rootScope.transform($scope.data), $rootScope.config)
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
    .controller('EventsCtrl', function ($rootScope, $scope, $http, $location, $mdDialog, $routeParams) {

        $scope.params = {name: 'Events', url: 'events', editurl: 'event'};
        console.log($scope.params.name + " Ctrl init");

        $scope.paging = {
            total: 1,
            current: 1,
            onPageChanged: function () {
                $scope.init();
            }
        };

        $scope.sortType = 'name'; // set the default sort type
        $scope.sortReverse = false;  // set the default sort order
        $scope.search = $routeParams.search;

        $scope.init = function () {
            console.log($scope.params.name + " Ctrl scope init");
            if (typeof $scope.search == 'undefined') {
                $scope.search = '';
            }
            $http.get("/api/v1/" + $scope.params.url + "?page=" + $scope.paging.current + "&unpublish=" + $scope.unPublishedEvents + "&search=" + $scope.search, $rootScope.config)
                .then(function (res) {
                    //console.log(res.data.response);
                    $scope.paging.total = res.data.response.last_page;
                    $scope.paging.current = res.data.response.current_page;
                    if (res.data.error == false) {
                        console.log($scope.params.name + " Ctrl data to view");
                        $scope.users = res.data.response.data;
                    } else {
                        console.log($scope.params.name + " Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log($scope.params.name + " Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };


        $scope.publish = function (event) {
            var id = event.id;
            console.log($scope.params.name + " Ctrl try publish event with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want publish event with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/' + $scope.params.url + '/publish/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            event.publish = 1;
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
                $rootScope.info('Publish event was aborted');
            });

        };
        $scope.unpublish = function (event) {
            var id = event.id;
            console.log($scope.params.name + " Ctrl try unpublish event with id" + id);
            var confirm = $mdDialog.confirm()
                .textContent('Your want unpublish event with id:' + id + '?')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.get('/api/v1/' + $scope.params.url + '/unpublish/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            event.publish = 0;
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
                $rootScope.info('UnPublish event was aborted');
            });

        };
        $scope.edit = function (id) {
            console.log($scope.params.name + ' Ctrl try edit ' + id);
            $location.path("/" + $scope.params.editurl + "/" + id);
        };
        $scope.delete = function (id) {
            console.log($scope.params.name + ' Ctrl try delete ' + id);
            var confirm = $mdDialog.confirm()
                .title('Really?')
                .textContent('Your want delete item with id:' + id + '?')
                .ariaLabel('Lucky day')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.delete('/api/v1/' + $scope.params.url + '/' + id + '', $rootScope.config).then(function (res) {
                    $scope.init();
                });
            }, function () {
                console.log('second')
            });
        };
    })
    .controller('EventCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout, $mdDialog) {
        /*PARAMS FOR CONTROLLER*/
        $scope.params = {name: 'Event', url: 'events'};

        $scope.requiredFields = ['title','description','time','date','type','category_id','date_stop'];

        $scope.cinema = [];

        console.log($scope.params.name + " Ctrl init");
        $scope.data = {};
        $scope.photos = [];
        $scope.init = function () {
            $scope.hideFields = [];
            $scope.cinema_block = false;
            console.log($scope.params.name + " Ctrl scope init");
            $http.get('api/v1/' + $scope.params.url + '/s/edit', $rootScope.config)
                .then(function (res) {
                    console.log($scope.params.name + '  Ctrl loading schema');
                    //console.log(res.data.response);
                    $scope.schema = res.data.response;
                    //console.log($scope.schema);
                    angular.forEach(res.data.response, function (v, i) {
                        $scope.data[v.key];
                    });
                    //console.log($scope.data);
                });
            $scope.id = $routeParams.id;

            $http.get('api/v1/categories', $rootScope.config)
                .then(function (res) {
                    console.log('Try load list of categories');
                    $scope.categories_select = res.data.response;
                    $scope.changeCategory($scope.data.category_id);
                });

            if (typeof $scope.id == 'undefined') {
                $scope.id = 'new';
            } else {
                $http.get('/api/v1/' + $scope.params.url + '/' + $scope.id, $rootScope.config).then(function (res) {
                    //console.log(res);
                    if (res.data.error == true) {
                        $rootScope.error('Event not found, you were redirected to created new.');
                        $timeout(function () {
                            $location.path('/event/');
                        }, 3000);

                    }
                    $scope.data = res.data.response;
                    //transform data
                    $scope.data.date = new Date($scope.data.date);
                    //$scope.data.date = $rootScope.dateToISO($scope.data.date);
                    //console.log($scope.data.cinema);
                    angular.forEach($scope.data.cinema, function (v, i) {
                        var findUser = false;
                        var user = v;
                        angular.forEach($scope.cinema, function (v, i) {
                            if (v.id == user.user_id) {
                                v.sessions.push(
                                    {
                                        date: new Date(user.date),
                                        price: user.price
                                    });
                                findUser = true;
                            }
                        });
                        if (findUser == false) {
                            $scope.cinema.push(
                                {
                                    id: user.user_id,
                                    sessions: [
                                        {
                                            date: new Date(user.date),
                                            price: user.price
                                        }
                                    ]
                                }
                            );
                        }
                    });

                    $scope.data.date_stop = new Date($scope.data.date_stop);
                    $scope.data.publish = $scope.data.publish ? true : false;
                    if ($scope.data.price_range.from !== $scope.data.price_range.to) {
                        $scope.data.price = $scope.data.price_range.from + '..' + $scope.data.price_range.to;
                    }

                    $scope.photos = res.data.response.photos;
                    //console.log($scope.data.publish);
                }, function (res) {
                    $rootScope.error('Request get categories list was failed');
                });
            }
            console.log($scope.params.name + ' Ctrl try edit id ' + $scope.id);
        };
        $scope.changeCategory = function (category_id) {

            var keepGoing = true;
            angular.forEach($scope.categories_select, function (v, i) {
                if (keepGoing) {
                    if (v.id == category_id) {
                        if (v.show == 'cinema') {
                            $scope.cinema_block = true;
                            $scope.hideFields = ['address','phone_1','phone_2','price'];
                            keepGoing = false;
                        } else {
                            $scope.cinema_block = false;
                            $scope.hideFields = [];
                        }
                    }
                }
            });

        };
        $scope.addUserToCinema = function (ev) {
            var confirm = $mdDialog.prompt()
                .title('Введите ID кинотеатра')
                .ok('ОК');
            $mdDialog.show(confirm).then(function (result) {
                var double = false;
                angular.forEach($scope.cinema, function (v, i) {
                    if (v.id == result) {
                        double = true;
                    }
                });
                if (double || typeof result == "undefined") {
                    $rootScope.warning('ID Этого кинотеатра уже существует либо id повреждён.');
                } else {
                    $scope.cinema.push(
                        {
                            id: result,
                            sessions: [
                                {date: "2017-01-03", price: 10}
                            ]
                        }
                    );
                }

            });

        };
        $scope.removeCinema = function (item) {
            var index = $scope.cinema.indexOf(item);
            $scope.cinema.splice(index, 1);
        };
        $scope.addSession = function (cinema) {
            var index = $scope.cinema.indexOf(cinema);
            $scope.cinema[index].sessions.push({
                date: null,
                price: 10
            });
        };

        $scope.removeSession = function (cinema, session) {
            var cinemaIndex = $scope.cinema.indexOf(cinema);
            cinema = $scope.cinema[cinemaIndex];
            var sessionIndex = cinema.sessions.indexOf(session);
            $scope.cinema[cinemaIndex].sessions.splice(sessionIndex, 1);
        };

        $scope.removePhoto = function (photo) {
            //console.log(photoId);
            if (photo.id == null) {
                var index = $scope.photos.indexOf(photo);
                $scope.photos.splice(index, 1);
            } else {
                $http.get('/api/v1/photo/' + photo.id + '/remove', $rootScope.config).then(function (res) {
                    $scope.init();
                });
            }


        };
        /*upload image*/
        $scope.upload = function (file) {
            //console.log(file);
            console.log($scope.params.name + ' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name + ' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name + ' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                            //event:$scope.id
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if (res.status == 200) {
                            //$scope.uploadedFile = res.data;
                            $rootScope.success('OK');
                            $scope.photos.push({"image": res.data, "id": null});

                            //console.log($scope.photos);
                            //$scope.init();
                        }
                    });
                }

            }
        };
        $scope.save = function () {
            //console.log($scope.data);
            $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
            console.log($scope.params.name + ' Ctrl try save user with id:' + $scope.id);

            var photos = [];
            angular.forEach($scope.photos, function (v, i) {
                //console.log(v);
                photos.push(v.image);
            });
            $scope.data.images = photos;

            if ($scope.cinema_block) {
                $scope.data.cinema = angular.toJson($scope.cinema);
            }

            //console.log(date);
            if ($scope.id !== 'new') {
                //console.log($scope.data);
                //$rootScope.transform(
                $http.post('/api/v1/' + $scope.params.url + '/' + $scope.id, $rootScope.serialize($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            //$scope.id
                            $rootScope.success('OK');
                            $location.path('/' + $scope.params.url + '');
                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            } else {
                                msg = res.data.message;
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            } else {
                //console.log($scope.data);
                console.log($scope.params.name + ' Ctrl try save new row');

                $http.post('/api/v1/' + $scope.params.url + '', $rootScope.serialize($scope.data), $rootScope.config)
                    .then(function (res) {
                        if (res.data.error == false) {
                            $rootScope.success('OK');
                            $location.path('/event/' + res.data.message);

                        } else {
                            if (res.data.message == 'valid') {
                                var msg = '';
                                angular.forEach(res.data.validator, function (v, i) {
                                    msg += v + '<br>';
                                })
                            } else {
                                msg = res.data.message;
                            }
                            $rootScope.warning(msg);
                        }
                    }, function (res) {
                        $rootScope.error('Request return failed');
                    });
            }

        }
    })

    .controller('PushHistoryCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout, $mdDialog) {
        $scope.params = {name: 'Push', url: 'push'};
        console.log($scope.params.name + " Ctrl init");
        $scope.data = {};
        $scope.search = $routeParams.search;
        $scope.restrict_max_size = true;

        $scope.page = 0;
        $scope.per_page = 150;
        $scope.$watch('restrict_max_size', function (new_value, old_value) {
            if (new_value) {
                $scope.per_page = 300;
            } else {
                $scope.per_page = 9999999;
            }
            $scope.init();
        });


        $scope.init = function () {
            console.log($scope.params.name + " Ctrl scope init");
            $http.get("/api/v1/" + $scope.params.url + "?page=" + $scope.page + "&per_page=" + $scope.per_page)
                .then(function (res) {
                    //console.log(res.data.response[0])
                    if (res.data.error == false) {
                        console.log($scope.params.name + " Ctrl data to view");
                        $scope.users = res.data.response;
                    } else {
                        console.log($scope.params.name + " Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log($scope.params.name + " Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };
        $scope.delete = function (id) {
            console.log($scope.params.name + ' Ctrl try delete ' + id);
            var confirm = $mdDialog.confirm()
                .title('Really?')
                .textContent('Your want delete item with id:' + id + '?')
                .ariaLabel('Lucky day')
                .ok('Please do it!')
                .cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.delete('/api/v1/' + $scope.params.url + '/' + id, $rootScope.config)
                    .then(function (res) {
                        if (res.data.error !== true) {
                            $rootScope.success('Request is done');
                            $http.get("/api/v1/" + $scope.params.url + "")
                                .then(function (res) {
                                    //console.log(res.data.response[0]);
                                    if (res.data.error == false) {
                                        console.log($scope.params.name + " Ctrl data to view");
                                        $scope.users = res.data.response;
                                    } else {
                                        console.log($scope.params.name + " Ctrl data have error");
                                        $rootScope.warning('Request return error');
                                    }
                                }, function (res) {
                                    console.log($scope.params.name + " Ctrl bad request");
                                    $rootScope.error('Request failed');
                                });
                        } else if (res.data.error == true) {
                            console.log(res);
                            $rootScope.warning('Request return error try with:' + res.data.response + '<br>' + res.data.message);
                        }
                    }, function (res) {
                        console.log(res);
                        $rootScope.error('Request return error code');
                    });
            }, function () {
                $rootScope.info('Request was aborted');
            });
        };
    })
    .controller('PushCtrl', function ($rootScope, $scope, $http, $routeParams, $location, Upload, $timeout, uiGmapGoogleMapApi) {
        $scope.params = {name: 'Push', url: 'push'};
        console.log($scope.params.name + " Ctrl init");
        $scope.data = {};
        $scope.sendFor = [];

        //$scope.categories = [{name_EN: 'Loading...'}];
        $scope.map = {center: {latitude: 55, longitude: 37}, zoom: 4};
        $scope.markers = [];
        uiGmapGoogleMapApi.then(function (maps) {
            $scope.maps = maps;
        });
        $scope.init = function () {
            console.log("Categories Ctrl scope init");
            $http.get("/api/v1/categories", {ignoreLoadingBar: true})
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log("Push Ctrl data to view");
                        $scope.categories = res.data.response;
                        $scope.$apply();
                        //console.log($scope.categories);
                    } else {
                        console.log("Push Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log("Push Ctrl bad request");
                    $rootScope.error('Request failed');
                });

        };

        $scope.headers = {
            headers: {
                'token': 'adm',
                'Content-Type': 'application/json'
            }
        }

        $scope.sendPushes = function () {
            $scope.pushData = {push: $scope.push, users: $scope.sendFor};
            $http.post('/api/v1/push/send/system', $scope.pushData, $scope.headers)
                .then(function (res) {
                    if (res.data.error == false) {
                        var msg = '';
                        angular.forEach(res.data.message, function (v, i) {
                            msg += v + '<br>';
                        })
                        $rootScope.success(msg);
                        $scope.push.title = '';
                        $scope.push.description = '';

                    } else {
                        if (res.data.message == 'valid') {
                            var msg = '';
                            angular.forEach(res.data.validator, function (v, i) {
                                msg += v + '<br>';
                            })
                        } else {
                            msg = res.data.message;
                        }
                        $rootScope.warning(msg);
                    }
                }, function (res) {
                    $rootScope.error('Request return failed');
                });
        }

        $scope.upload = function (file) {
            console.log($scope.params.name + ' Ctrl  try upload' + file);
            if (file) {
                console.log($scope.params.name + ' Ctrl  file exists');
                var file = file;
                if (!file.$error) {
                    console.log($scope.params.name + ' Ctrl  go upload');
                    Upload.upload({
                        url: '/images/upload',
                        data: {
                            image: file
                        }
                    }).then(function (res) {
                        //console.log(res);
                        if (res.status == 200) {
                            $scope.push.image = res.data;
                        }
                    });
                }

            }
        };

        $scope.checkMarkers = function () {
            //console.log($scope.markers.length)
            if ($scope.sendFor.length > 0 && $scope.push.title.length > 0 && $scope.push.description.length > 0 && $scope.push.image.length > 0) {
                return false;
            } else {
                return true;
            }
        }

        $scope.showUsersInMap = function () {
            console.log("Push Ctrl show users in map");
            $scope.markers = [];
            $http.get("/api/v1/users/push/get/" + $scope.category_selected, $rootScope.config)
                .then(function (res) {
                    if (res.data.error == false) {
                        console.log("Push Ctrl data to view");
                        angular.forEach(res.data.response, function (v, key) {
                            //console.log(v.id)
                            $scope.markers.push({
                                id: v.id,
                                latitude: v.lat,
                                longitude: v.lon
                            });
                        });
                        //console.log($scope.circles);

                    } else {
                        console.log("Push Ctrl data have error");
                        $rootScope.warning('Request return error');
                    }
                }, function (res) {
                    console.log("Push Ctrl bad request");
                    $rootScope.error('Request failed');
                });
        };

        $scope.options = {scrollwheel: false};
        $scope.circles = [
            {
                id: 1,
                center: {
                    latitude: 55,
                    longitude: 37
                },
                radius: 500000,
                stroke: {
                    color: '#08B21F',
                    weight: 2,
                    opacity: 1
                },
                fill: {
                    color: '#08B21F',
                    opacity: 0.5
                },
                geodesic: true, // optional: defaults to false
                draggable: true, // optional: defaults to false
                clickable: true, // optional: defaults to true
                editable: true, // optional: defaults to false
                visible: true, // optional: defaults to true
                control: {},
                events: {
                    dragend: function () {
                        //console.log();
                        $scope.sendFor = [];
                        var circle = new $scope.maps.LatLng($scope.circles[0].center.latitude, $scope.circles[0].center.longitude);
                        angular.forEach($scope.markers, function (v, key) {
                            var marker = new $scope.maps.LatLng(v.latitude, v.longitude);
                            var dist = $scope.maps.geometry.spherical.computeDistanceBetween(circle, marker);
                            if (dist < $scope.circles[0].radius) {
                                $scope.sendFor.push(v);
                            }
                        });


                        //console.log($scope.circles[0].center.latitude);
                    }
                }
            }
        ];


    });