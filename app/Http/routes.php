<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/*
change log
0.1.5
- Events 
0.1.4
- Пользователь теперь может быть забанен в Админ панели

0.1.3
- Баланс теперь в рабочем состоянии
- У категорий появилась цена добавления эвента
- Загрузка картинок
- Добавлена Админ панель ( просмотр категорий и пользователей )
*/

Route::get('/',function(){
    return view('admin.index');
});
Route::group(['prefix' => 'api'], function () {
    Route::get('{code}', function ($code) {
        $response = ['error' => true, 'message' => null];
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

    Route::group(['prefix' => 'v1'], function () {
        Route::resource('users', 'UsersController');
        Route::post('users/auth/{type}', 'UsersController@auth');

        Route::resource('categories', 'CategoriesController');
        Route::resource('events', 'EventsController',['except' => ['update']]);

        Route::group(['middleware' => [\App\Http\Middleware\AuthByToken::class]], function () {
            Route::post('events', 'EventsController@store_save');
            Route::put('events/{id}', 'EventsController@update_save');

            Route::post('events/follow', 'EventsController@follow');
            Route::get('users/events/favorite', 'EventsController@showFavorite');
            Route::post('events/unfollow', 'EventsController@unfollow');

            Route::post('categories/favorite', 'CategoriesController@favorite');
            Route::post('categories/unfavorite', 'CategoriesController@unfavorite');

            Route::get('/users/ban/{id}','UsersController@ban');
            Route::get('/users/unban/{id}','UsersController@unban');
        });


    });

});

Route::post('/images/upload','Controller@uploadFile');

Route::get('images/{filename}', function ($filename) {
    $path = storage_path() . '/app/public/images/' . $filename;
    if(file_exists($path)) {
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
    }else{
        $response = array('error'=>true,'message'=>'not found image');
    }


    return $response;
});