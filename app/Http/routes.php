<?php

Route::get('/',function(){
    return view('welcome');
});
Route::get('test',function(){

    return view('test');
});
Route::get('/admin', function () {
    return view('admin.index');
})->middleware([\App\Http\Middleware\AuthOnce::class]);

Route::group([ 'prefix' => 'api','middleware' =>\App\Http\Middleware\Cors::class ], function () {
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
    Route::post('reset/password','UsersController@resetPasswordRequest');
    Route::get('reset/password/{token}','UsersController@resetPassword');

    Route::post('payment/callback','PayController@callback');

    Route::group([ 'prefix' => 'v1' ], function () {
        Route::resource('users', 'UsersController', [ 'expect' => [ 'update', 'show' ] ]);
        Route::post('users/auth/{type}', 'UsersController@auth');
        Route::post('users/password/change/{user}', 'UsersController@changePassword');


        Route::resource('categories', 'CategoriesController', [ 'except' => [ 'show' ] ]);
        Route::get('category/{id}','CategoriesController@show');

        Route::get('category/{id}/users','EventsController@getPostedUsersInCategory');


        Route::get('event/{id}/images','EventsController@getImagesFromEvent');

        Route::resource('push','PushController');
        Route::get('push/send/user/{id}','PushController@sendForUser');
        Route::group([ 'middleware' => [ \App\Http\Middleware\AuthByToken::class ] ], function () {
            Route::resource('events', 'EventsController', [ 'except' => [ 'update' ] ]);

            Route::get('events/{id}/cinema','EventsController@getCinemaByEvent');

            Route::post('events', 'EventsController@store_save');
            Route::post('events/{id}', 'EventsController@update_save');


            Route::post('events/{id}/comment','EventsController@comment');

            Route::get('photo/{id}/remove','EventsController@removePhoto');

            Route::post('users/events/follow', 'EventsController@follow');
            Route::post('users/events/unfollow', 'EventsController@unfollow');
            Route::get('users/events/follow','EventsController@followsEvents');
            Route::get('users/events/favorite/{place_id?}', 'EventsController@showFavorite');
            Route::get('events/byuser/{id}','EventsController@showByUser');

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


            Route::post('payments/pay','PayController@pay');

            Route::post('invoice','PayController@invoice');
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
        $response = array( 'error' => true, 'message' => 'Not found image' );
    }
    return $response;
});

Route::get('images/{filename}/thumbnail',function($filename){
    $pathOriginal = storage_path() . '/app/public/images/' . $filename;
    $pathThumbnail = storage_path() . '/app/public/images/thumbnail_200_' . $filename.'';
    if(file_exists($pathThumbnail)){
        $file = File::get($pathThumbnail);
        $type = File::mimeType($pathThumbnail);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }else{
        if(file_exists($pathOriginal)){
            $img = Image::make($pathOriginal);
            $img->resize(null, 200, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save(storage_path() . '/app/public/images/thumbnail_200_' . $filename);
            return $img->response('jpg');
        }else{
            return array( 'error' => true, 'message' => 'Not found image' );
        }

    }
});