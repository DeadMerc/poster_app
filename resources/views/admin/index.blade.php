<!DOCTYPE html>
<html lang="en" ng-app="adminApp">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PosterApp admin panel</title>
    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
    <link rel="stylesheet"
          href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0-rc2/angular-material.min.css">
    <!--AngularJS-->
    <script src="https://cdn.jsdelivr.net/lodash/4.14.1/lodash.min.js"></script>

    <!--<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.12/angular.min.js"></script>-->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js"></script>
    <!--<script src="https://code.angularjs.org/1.4.0-rc.0/angular.js"></script>-->
    <script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0-rc2/angular-material.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-animate.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-aria.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular-messages.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/t-114/svg-assets-cache.js"></script>
    <!-- Toasts -->
    <script src="https://unpkg.com/angular-toastr/dist/angular-toastr.tpls.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/angular-toastr/dist/angular-toastr.css"/>
    <!-- Loading bar -->
    <link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/angular-loading-bar/0.9.0/loading-bar.min.css'
          type='text/css' media='all'/>
    <script type='text/javascript'
            src='//cdnjs.cloudflare.com/ajax/libs/angular-loading-bar/0.9.0/loading-bar.min.js'></script>
    <!-- Routes -->
    <script src="http://angular.github.io/angular-phonecat/step-7/app/bower_components/angular-route/angular-route.js"></script>
    <!-- Uploader -->
    <script src="https://dl.dropboxusercontent.com/u/105655494/libs/ng-file-upload-shim.js"></script>
    <script src="https://dl.dropboxusercontent.com/u/105655494/libs/ng-file-upload.js"></script>
    <!-- paginate -->
    <script src="https://rawgit.com/Crawlink/material-angular-paging/master/build/dist.min.js"></script>
    <!-- Table sorter -->
    <link rel="stylesheet" href="https://cdn.rawgit.com/angular-ui/bower-ui-grid/master/ui-grid.min.css">
    <script src="https://cdn.rawgit.com/angular-ui/bower-ui-grid/master/ui-grid.min.js"></script>
    <!--Google map -->
    <script src="https://dl.dropboxusercontent.com/u/105655494/js/logger.js"></script>
    <script src="js/maps.js"></script>
    <script src='//maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry&key=AIzaSyAERAEhHw6pPKhUpee6ofTz8f3qZUhUgLU'></script>


    <!--<script src="https://cdn.jsdelivr.net/angular.google-maps/1.2.4/angular-google-maps.min.js"></script>-->
    <!-- App -->
    <script src="js/app.js"></script>
    <script src="js/controllers.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--https://dl.dropboxusercontent.com/u/105655494/-->
    <link rel="stylesheet" href="css/theme.css">

</head>

<body>

<div id="wrapper">

    <md-content>
        <md-toolbar class="md-hue-2">
            <div class="md-toolbar-tools">
                <md-button class="md-icon-button" aria-label="Settings" ng-click="toggleLeft()">
                    <md-icon md-svg-icon="img/icons/menu.svg"></md-icon>
                </md-button>
                <h2>
                    <span>Poster</span>
                </h2>
                <span flex></span>
            </div>
        </md-toolbar>
    </md-content>

    <md-sidenav class="md-sidenav-left" md-component-id="left"
                md-whiteframe="4">

        <md-toolbar class="md-theme-indigo">
            <h1 class="md-toolbar-tools">Меню</h1>
        </md-toolbar>

        <md-content layout-margin>
            <md-list flex>
                <md-list-item class="md-3-line">
                    <div class="md-list-item-text" layout="column">
                        <h3><a href="#/categories">Категории</a><a class="sub-menu" href="#/category"><i
                                        class="material-icons correct_icon">add_box</i></a></h3>
                    </div>

                </md-list-item>

                <md-list-item class="md-3-line">
                    <div class="md-list-item-text" layout="column">
                        <h3><a href="#/users">Пользователи</a><a class="sub-menu" href="#/user"><i
                                    class="material-icons correct_icon">add_box</i></a></h3>
                    </div>
                </md-list-item>

                <md-list-item class="md-3-line">
                    <div class="md-list-item-text" layout="column">
                        <h3><a href="#/events">События</a><a class="sub-menu" href="#/event"><i
                                    class="material-icons correct_icon">add_box</i></a></h3>
                    </div>
                </md-list-item>

                <md-list-item class="md-3-line">
                    <div class="md-list-item-text" layout="column">
                        <h3><a href="#/push">Пуши</a><a class="sub-menu" href="#/push/history"><i
                                    class="material-icons correct_icon">history</i></a></h3>
                    </div>
                </md-list-item>
            </md-list>
        </md-content>

    </md-sidenav>
    <!-- Sidebar
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">

                PosterApp Меню

            </li>
            <li>
                <a class="sidebar-link" href="#/categories">Категории</a><a class="sub-menu" href="#/category"><i
                            class="material-icons correct_icon">add_box</i></a>
            </li>
            <li>
                <a class="sidebar-link" href="#/users">Пользователи</a><a class="sub-menu" href="#/user"><i class="material-icons correct_icon">add_box</i></a>
            </li>
            <li>
                <a class="sidebar-link" href="#/events">События</a><a class="sub-menu" href="#/event"><i class="material-icons correct_icon">add_box</i></a>
            </li>
            <li>
                <a class="sidebar-link" href="#/push">Пуши</a><a class="sub-menu" href="#/push/history"><i
                            class="material-icons correct_icon">history</i></a>
            </li>
        </ul>
    </div>-->
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->

    <div id="page-content-wrapper">

        <div class="container-fluid">
            <div class="row">
                <div flex>
                    <div ng-view></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->

</div>

</body>
</html>
