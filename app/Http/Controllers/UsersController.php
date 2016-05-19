<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use Illuminate\Validation\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return $this->helpReturn(User::with('events')->with('favorites')->get());
    }


    public function show($id) {
        return $this->helpReturn(User::with('events')->with('favorites')->findorfail($id));
    }
    /**
     * @api {post} /v1/users/auth/email AuthByEmail
     * @apiVersion 0.1.0
     * @apiName AuthByEmail
     * @apiGroup Users
     *
     * @apiParam {string} name
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
        if ($type == 'email') {
            $data['email'] = $request->email;
            $data['password'] = md5($request->password . $request->email . 'requestLoginEvstolia');
            $user = User::where('email', '=', $data['email'])->where('password', '=', $data['password'])->first();
            if ($user) {
                return $this->helpReturn($user);
            } else {
                return $this->helpError('Wrong credentials');
            }
        } elseif ($type == 'fb' or $type == 'vk') {
            $user = User::where('social_hash', '=', $request->social_hash)->first();
            if (!$user) {
                $user = new User;
                $user->social_hash = $request->social_hash;
                $user->name = $request->name;
                $user->token = md5(uniqid() . md5(date("h:m")));
                $user->type = 'social';
                $user->save();
                return $this->helpReturn($user);
            } else {
                return $this->helpReturn($user, null, 'hey');
            }
        } elseif ($type == 'hidden') {
            $imei = $request->imei;
            $user = User::where('imei', '=', $imei)->first();
            if (!$user) {
                $user = new User;
                $user->imei = $request->imei;
                $user->token = md5(uniqid() . md5(date("h:m")));
                $user->type = 'hidden';
                $user->save();
                return $this->helpReturn($user);
            } else {
                return $this->helpReturn($user);
            }
        }
    }

    public function create() {
        //
    }

    /**
     * @api {post} /v1/users/:type regUser
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
        $rules = ['name' => 'required|min:3', 'email' => 'required|unique:users', 'password' => 'required'];
        $valid = Validator($request->all(), $rules);
        if (!$valid->fails()) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = md5($request->password . $request->email . 'requestLoginEvstolia');
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
        return $this->getSchemaByModel(User::first());
    }


    public function update(Request $request, $id) {
        $rules = ['image'=>'image','name' => 'required|min:3', 'location' => 'required|min:3', 'lon' => 'required', 'lat' => 'required', 'category_id' => 'required', 'type' => 'required', 'email' => 'required', 'password' => 'required'];
        $user = User::findorfail($id);
        return $this->fromPostToModel($rules, $user, $request);
    }
    public function ban($id){
        $user = User::findorfail($id);
        $user->banned = 1;
        $user->save();
        return $this->helpReturn($user);
    }
    public function unban($id){
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
        //
    }
}
