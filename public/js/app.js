var adminApp = angular.module('adminApp', [
    'ngRoute',
    'adminControllers',
    'ngMaterial',
    'material.svgAssetsCache',
    'ngMessages',
    'toastr',
    'angular-loading-bar',
    'ngAnimate',
    'ngFileUpload',
    'ui.grid',
    'uiGmapgoogle-maps',
    'cl.paging'
], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

adminApp.factory('$myElementInkRipple', function($mdInkRipple) {
    return {
        attach: function (scope, element, options) {
            return $mdInkRipple.attach(scope, element, angular.extend({
                center: false,
                dimBackground: true
            }, options));
        }
    };
});

adminApp.run(function ($rootScope, toastr,$myElementInkRipple,$routeParams, $mdSidenav) {
    console.log("App started");
    $rootScope.onClick = function (ev) {
        $myElementInkRipple.attach($scope, angular.element(ev.target), { center: true });
    };
    /* config for http*/
    $rootScope.config = {
        headers: {
            'token': 'adm',
            'Content-Type': 'application/x-www-form-urlencoded',
            'lang':'RU'
        }
    };
    $rootScope.$on('$routeChangeStart', function(next, current) {
        $mdSidenav('left').close()
    });
    $rootScope.toggleLeft = buildToggler('left');

    function buildToggler(componentId) {
        return function() {
            $mdSidenav(componentId).toggle();
        }
    }
    $rootScope.keyToText = function (text){

        data = {
            "user_id":"ID пользователя",
            "publish":"Опубликовать",
            "category_id":"Категория",
            "place_id":"ID места",
            "title":"Заголовок",
            "description":"Описание",
            "date":"Дата начала события",
            "time":"Время события",
            "date_stop":"Дата, когда событие будет скрыто с приложения",
            "address":"Адресс",
            "type":"Тип",
            "video":"Ссылка на видео",
            "price":"Стоимость, диапазон цен:\"10..50\" ",
            "private":"Приватное",
            "public":"Публичное",
            "images":"Изображения",
            "back":"Назад",
            "save":"Сохранить",
            "upload":"Загрузить",
            "remove image":"Удалить",
            "select image":"Выбрать изображение",
            "edit":"Редактировать",
            "delete":"Удалить",
            "events":"События",
            "ban":"Заблокировать",
            "unban":"Разблокировать",
            "not defined":"Не установлено",
            "unpublish":"Скрыть",
            "phone_1":"Первый телефон",
            "phone_2":"Второй телефон",
            "show":"Показ",
            "users":"Пользователи",
            "lat":"Широта",
            "lon":"Долгота"
        };
        if(data.hasOwnProperty(text) && $routeParams.lang != 'en'){
            return data[text];
        }else{
            console.log("Not found:"+text);
            return text;
        }
    };
    /* toasts */
    $rootScope.success = function ($text) {
        //success warning info error
        toastr.success($text, {allowHtml: true});
    };
    $rootScope.warning = function ($text) {
        //success warning info error
        toastr.warning($text, {allowHtml: true});
    };
    $rootScope.info = function ($text) {
        //success warning info error
        toastr.info($text, {allowHtml: true});
    };
    $rootScope.error = function ($text) {
        //success warning info error
        toastr.error($text, {allowHtml: true});
    };
    $rootScope.transform = function (obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for (name in obj) {
            value = obj[name];

            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    $rootScope.serialize = function ( data ) {
        // If this is not an object, defer to native stringification.
        if ( ! angular.isObject( data ) ) {
            return( ( data == null ) ? "" : data.toString() );
        }

        var buffer = [];

        // Serialize each key in the object.
        for ( var name in data ) {
            if ( ! data.hasOwnProperty( name ) ) {
                continue;
            }

            var value = data[ name ];

            buffer.push(
                encodeURIComponent( name ) + "=" + encodeURIComponent( ( value == null ) ? "" : value )
            );
        }

        // Serialize the buffer and clean it up for transportation.
        var source = buffer.join( "&" ).replace( /%20/g, "+" );
        return( source );
    }
    /*
    TODO: ISO disabled
     */
    $rootScope.dateToISO = function(input) {
        //return input;
        return new Date(input).toISOString();
    };
});
adminApp.config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
    console.log("Spinner init");
    cfpLoadingBarProvider.includeSpinner = false;

}])

adminApp.config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
        key: 'AIzaSyAERAEhHw6pPKhUpee6ofTz8f3qZUhUgLU',
        v: '3.20', //defaults to latest 3.X anyhow
        libraries: 'weather,geometry,visualization'
    });
})

adminApp.config(['$routeProvider',
    function ($routeProvider) {
        console.log("Route Init");
        $routeProvider

            .when('/categories', {
                templateUrl: 'app/categories.html',
                controller: 'CategoriesCtrl'
            })
            .when('/category/:id?', {
                templateUrl: 'app/category.html',
                controller: 'CategoryCtrl'
            })
            .when('/users', {
                templateUrl: 'app/users.html',
                controller: 'UsersCtrl'
            })
            .when('/user/:id?', {
                templateUrl: 'app/user.html',
                controller: 'UserCtrl'
            })
            .when('/events', {
                templateUrl: 'app/events.html',
                controller: 'EventsCtrl'
            })
            .when('/event/:id?', {
                templateUrl: 'app/event.html',
                controller: 'EventCtrl'
            })
            .when('/push', {
                templateUrl: 'app/push.html',
                controller: 'PushCtrl'
            })
            .when('/push/history', {
                templateUrl: 'app/push_history.html',
                controller: 'PushHistoryCtrl'
            })

            .otherwise({
                redirectTo: '/'
            });
    }]);