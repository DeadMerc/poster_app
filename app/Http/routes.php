<?php
Route::get('/', function () {
    return view('admin.index');
});
Route::group([ 'prefix' => 'api' ], function () {
    Route::get('{code}', function ($code) {
        $response = [ 'error' => true, 'message' => null ];
        if ($code == 404) {
            $response['response'] = 'Not found token';
        } elseif ($code == 405) {
            $response['response'] = 'Failed token';
        } elseif ($code == 'ban') {
            $response['response'] = 'Your accound was banned';
        } else {
            $response['response'] = 'Dont know error';
        }
        return $response;
    });

    Route::group([ 'prefix' => 'v1' ], function () {
        Route::resource('users', 'UsersController', [ 'expect' => [ 'update', 'show' ] ]);
        Route::post('users/auth/{type}', 'UsersController@auth');


        Route::resource('categories', 'CategoriesController', [ 'except' => [ 'show' ] ]);
        Route::resource('events', 'EventsController', [ 'except' => [ 'update' ] ]);
        Route::resource('push','PushController');

        Route::group([ 'middleware' => [ \App\Http\Middleware\AuthByToken::class ] ], function () {
            Route::post('events', 'EventsController@store_save');
            Route::post('events/{id}', 'EventsController@update_save');

            Route::post('users/events/follow', 'EventsController@follow');
            Route::get('users/events/favorite', 'EventsController@showFavorite');
            Route::post('events/unfollow', 'EventsController@unfollow');
            Route::get('events/publish/{id}', 'EventsController@publish');
            Route::get('events/unpublish/{id}', 'EventsController@unpublish');

            Route::post('categories/favorite', 'CategoriesController@favorite');
            Route::post('categories/unfavorite', 'CategoriesController@unfavorite');
            Route::get('categories/favorites', 'CategoriesController@favorites');

            Route::get('users/push/get/{id}','UsersController@forPush');
            Route::get('users/ban/{id}', 'UsersController@ban');
            Route::get('users/unban/{id}', 'UsersController@unban');
            Route::post('users/{id}', 'UsersController@update');

            Route::post('push/send/system','PushController@send');
        });


    });

});

Route::post('/images/upload', 'Controller@uploadFile');

Route::get('images/{filename}', function ($filename) {
    $path = storage_path() . '/app/public/images/' . $filename;
    if (file_exists($path)) {
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
    } else {
        $response = array( 'error' => true, 'message' => 'not found image' );
    }


    return $response;
});