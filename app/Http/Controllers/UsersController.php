<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use League\Flysystem\Exception;
use Mail;
class UsersController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @api {get} /v1/users/:id getUser
     * @apiVersion 0.1.0
     * @apiName getUser
     * @apiGroup Users
     * @apiHeader {string} token User token
     *
     */
    public function index(Request $request) {
        return $this->helpReturn(
            User::with('events')
                ->with('favorites')
                ->when($request->search,function($q)use($request){
                    $request->search = urldecode($request->search);
                    //dd($request->search);
                    return $q->orWhere('location','like','%'.$request->search.'%')
                        ->orWhere('phone_1','like','%'.$request->search.'%')
                        ->orWhere('phone_2','like','%'.$request->search.'%')
                        ->orWhere('phone_3','like','%'.$request->search.'%')
                        ->orWhere('email','like','%'.$request->search.'%')
                        ->orWhere('name','like','%'.$request->search.'%');
                })
                ->orderBy('id','desc')
                ->paginate(50)
        );
    }

    public function show($id) {
        return $this->helpReturn(User::with('events')->with('favorites')->findorfail($id));
    }

    public function forPush(Request $request, $category_id) {
        if($category_id == 'all') {
            //dd(User::where('lon', '>', 0)->where('lat', '>', 0)->get());
            return $this->helpReturn(DB::table('users')
                ->select('users.*')
                ->where('users.lat','>',0)
                ->where('users.lon','>',0)
                ->get());
        } else {
            $users = DB::table('users')
                ->join('categories_favorite', 'categories_favorite.user_id', '=', 'users.id')
                ->select('users.*', 'categories_favorite.category_id')
                ->where('categories_favorite.category_id',$category_id)
                ->where('users.lat','>',0)
                ->where('users.lon','>',0)
                ->get();

            /*SELECT  `users` . * ,  `categories_favorite`.`category_id`
FROM  `users`
INNER JOIN  `categories_favorite` ON  `categories_favorite`.`user_id` =  `users`.`id`
WHERE  `categories_favorite`.`category_id` =  '100'*/
            //dd($users);
            return $this->helpReturn($users);
            //return $this->helpReturn(User::where('lon', '>', "0")->where('lat', '>', "0")->where('category_id', $category_id)->get());
        }

    }


    /**
     * @api {post} /v1/users/auth/email AuthByEmail
     * @apiVersion 0.1.0
     * @apiName AuthByEmail
     * @apiGroup Users
     *
     * @apiParam {string} email
     * @apiParam {string} password
     *
     */
    /**
     * @api {post} /v1/users/auth/:type AuthBySocial
     * @apiVersion 0.1.0
     * @apiName AuthBySocial
     * @apiGroup Users
     *
     * @apiParam {string='vk','fb','hidden'} type Hidden=если пользователь ни как не авторизировался для учёта инфы
     * @apiParam {string} name
     * @apiParam {string} social_hash
     *
     */
    /**
     * @api {post} /v1/users/auth/hidden AuthByHidden
     * @apiVersion 0.1.0
     * @apiName AuthByHidden
     * @apiGroup Users
     *
     * @apiParam {string} imei
     *
     */
    public function auth(Request $request, $type = 'email') {
        if($type == 'email') {
            $data['email'] = $request->email;
            $data['password'] = md5($request->password . 'requestLoginEvstolia');
            $user = User::where('email', '=', $data['email'])->where('password', '=', $data['password'])->first();

            if($user) {
                $this->checkPushToken($request,$user);
                return $this->helpReturn($user);
            } else {
                return $this->helpError('Wrong credentials');
            }
        } elseif($type == 'fb' or $type == 'vk') {
            if($request->social_hash){
                $user = User::where('social_hash', '=', $request->social_hash)->first();
                if(!$user) {
                    $user = new User;
                    $user->social_hash = $request->social_hash;
                    $user->name = $request->name;
                    $user->token = md5(uniqid() . md5(date("h:m")));
                    $user->type = 'social';
                    $user->save();
                    $this->checkPushToken($request,$user);
                    return $this->helpReturn($user);
                } else {
                    return $this->helpReturn($user, null, 'hey');
                }
            }else{
                return $this->helpError('Wrong or empty hash');
            }
        } elseif($type == 'hidden') {
            $imei = $request->imei;
            $user = User::where('imei', '=', $imei)->first();
            if(!$user) {
                $user = new User;
                $user->imei = $request->imei;
                $user->token = md5(uniqid() . md5(date("h:m")));
                $user->type = 'hidden';
                $user->save();
                $this->checkPushToken($request,$user);
                return $this->helpReturn($user);
            } else {
                return $this->helpReturn($user);
            }
        }
    }

    protected function checkPushToken($request, $user) {
        if($request->device_type AND $request->device_token) {
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            $user->save();
        }
    }

    /**
     * @api {post} /reset/password resetPassword
     * @apiVersion 0.1.0
     * @apiName resetPassword
     * @apiGroup Users
     *
     * @apiParam {string} email
     *
     */
    public function resetPasswordRequest(Request $request){
        if(!$request->email){
            throw new Exception('Email not found',100);
        }
        $user = User::where('email',$request->email)->first();
        if($user){
            /*
            mail(
                $user->email,
                'Reset password request',
                $_SERVER['SERVER_NAME'].'/api/reset/password/'.$user->token
            );*/
            $mail = Mail::raw($_SERVER['SERVER_NAME'].'/api/reset/password/'.$user->token, function ($m) use ($user) {
                $m->from('no-reply@posterapp.com.ua', 'Poster');
                $m->to($user->email, $user->name)->subject('Reset Password Request');
            });
            return $this->helpInfo($mail);
        }else{
            return $this->helpError('Not found email');
        }
    }

    public function resetPassword($token){
        $user = User::where('token',$token)->first();
        if($user){
            $password = uniqid();
            $user->password = md5($password . 'requestLoginEvstolia');
            $user->token = md5(uniqid() . md5(date("h:m")));
            /*
            mail(
                $user->email,
                'You new password',
                $password
            );*/
            $mail = Mail::raw($password, function ($m) use ($user) {
                $m->from('no-reply@posterapp.com.ua', 'Poster');
                $m->to($user->email, $user->name)->subject('You new password');
            });
            $user->save();
            echo 'New password in your mailbox.';
        }else{
            echo 'Bad token';
        }
    }

    public function create() {
        //
    }

    /**
     * @api {post} /v1/users regUser
     * @apiVersion 0.1.0
     * @apiName regUser
     * @apiGroup Users
     *
     * @apiDescription Только для авторизации по имейлу нужна регистрация,в остальных случаях она автоматическая
     * @apiParam {string} name
     * @apiParam {string} email
     * @apiParam {string} password
     *
     */
    public function store(Request $request) {
        $rules = ['name' => 'required|min:3', 'email' => 'required', 'password' => 'required'];
        $valid = Validator($request->all(), $rules);
        if(!$valid->fails()) {
            if(User::where('email',$request->email)->first()){
                throw new Exception('User already was registered.',100);
            }
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = md5($request->password . 'requestLoginEvstolia');
            $user->token = md5(uniqid() . md5(date("h:m")));
            $user->type = 'email';
            $user->save();
            return $this->helpReturn($user);
        } else {
            return $this->helpError('valid', $valid);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        return $this->getSchemaByModel(User::first(), ['type']);
    }

    /**
     * @api {post} /v1/users/:id updateUser
     * @apiVersion 0.1.0
     * @apiName updateUser
     * @apiGroup Users
     *
     * @apiHeader {string} token User token
     * @apiDescription При редактировании, если нужно какое-то определённое поле, в других должно быть false (bool)
     * @apiParam {string} name
     * @apiParam {file} image
     * @apiParam {string} email
     * @apiParam {string} password
     * @apiParam {string} [phone_1]
     * @apiParam {string} [phone_2]
     * @apiParam {string} [phone_3]
     * @apiParam {string} location
     * @apiParam {string} lon
     * @apiParam {string} lat
     * @apiParam {string} category_id
     * @apiParam {string} place_id
     * @apiParam {string} email
     * @apiParam {string} password
     * @apiParam {string} description
     *
     * @apiParam {string="ios","android"} device_type для пушей
     * @apiParam {string} device_token
     *
     */
    public function update(Request $request, $id) {
        //return var_dump($request->all());
        $rules = ['device_type'  => false,
                  'device_token' => false,
                  'balance'      => false,
                  'description'  => false,
                  'image'        => false,
                  'description'  => false,
                  'phone_1'      => false,
                  'phone_2'      => false,
                  'phone_3'      => false,
                  'name'         => false,
                  'location'     => false,
                  'lon'          => false,
                  'lat'          => false,
                  'category_id'  => false,
                  'email'        => false,
                  'password'     => false,
                  'place_id'     => false,
        ];

        $user = User::findorfail($id);
        return $this->fromPostToModel($rules, $user, $request);
    }
    /**
     * @api {post} /v1/users/password/change/{id} userChangePassword
     * @apiVersion 0.1.0
     * @apiName userChangePassword
     * @apiGroup Users
     *
     * @apiParam {string} password_old
     * @apiParam {string} password_new_first
     * @apiParam {string} password_new_second
     *
     */
    public function changePassword(Request $request,User $user){
        if($request->password_old){
            $password = md5($request->password_old . 'requestLoginEvstolia');
            if($user->password == $password){
                if($request->password_new_first == $request->password_new_second){
                    $user->password = md5($request->password_new_first . 'requestLoginEvstolia');
                    $user->save();
                    return $this->helpInfo();
                }
            }else{
                throw new Exception('Old password is wrong',100);
            }
        }else{
            throw new Exception('Not enough params',100);
        }
    }

    public function ban($id) {
        $user = User::findorfail($id);
        $user->banned = 1;
        $user->save();
        return $this->helpReturn($user);
    }

    public function unban($id) {
        $user = User::findorfail($id);
        $user->banned = 0;
        $user->save();
        return $this->helpReturn($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $user = User::findorfail($id)->delete();
        return $this->helpReturn($user);
    }
}
