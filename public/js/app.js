var adminApp = angular.module('adminApp', [
    'ngRoute',
    'adminControllers',
    'ngMaterial',
    'ngMessages',
    'toastr',
    'angular-loading-bar',
    'ngAnimate'
], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});


adminApp.run(function ($rootScope, toastr) {
    console.log("App started");
    /* toasts */
    $rootScope.success = function($text){
        //success warning info error
        toastr.success($text);
    };
    $rootScope.warning = function($text){
        //success warning info error
        toastr.warning($text);
    };
    $rootScope.info = function($text){
        //success warning info error
        toastr.info($text);
    };
    $rootScope.error = function($text){
        //success warning info error
        toastr.error($text);
    };

});
adminApp.config(['cfpLoadingBarProvider', function(cfpLoadingBarProvider) {
    cfpLoadingBarProvider.includeSpinner = false;
}])
adminApp.config(['$routeProvider',
    function ($routeProvider) {
        console.log("Route Init");
        $routeProvider.when('/categories', {
            templateUrl: 'app/categories.html',
            controller: 'CategoriesCtrl'
        }).when('/category/:id?', {
            templateUrl: 'app/category.html',
            controller: 'CategoryCtrl'
        }).when('/users', {
            templateUrl: 'app/users.html',
            controller: 'UsersCtrl'
        }).when('/user/:id?', {
            templateUrl: 'app/user.html',
            controller: 'UserCtrl'
        }).otherwise({
            redirectTo: '/'
        });
    }]);