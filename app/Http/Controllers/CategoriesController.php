<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Category_favorite;
use App\Http\Requests;
use Illuminate\Validation\Validator;

class CategoriesController extends Controller
{
    /**
     * @api {get} /v1/categories getCategories
     * @apiVersion 0.1.0
     * @apiName getCategories
     * @apiGroup Categories
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
     * @api {get} /v1/categories/favorite favoriteCategories
     * @apiVersion 0.1.0
     * @apiName favoriteCategories
     * @apiGroup Categories
     *
     * @apiHeader {string} token User token
     * @apiParam {array} category_ids example=['1','2']
     *
     *
     */
    public function favorite(Request $request){
        $valid = Validator($request->all(),['category_ids'=>'required']);
        if(!$valid->fails()){
            foreach ($request->category_ids as $id){
                $favorite = new Category_favorite;
                $favorite->category_id = $id;
                $favorite->user_id = $request->user->id;
                $favorite->save();
            }
            return $this->helpInfo();
        }else{
            return $this->helpError('valid',$valid);
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
    public function unfavorite(Request $request){
        $valid = Validator($request->all(),['category_id'=>'required']);
        if(!$valid->fails()){
            $favorite = Category_favorite::findorfail($request->categofy_id);
            $favorite->delete();
            return $this->helpInfo();
        }else{
            return $this->helpError('valid',$valid);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    
    public function store(Request $request) {
        $rules = ['name_RU'=>'required','name_EN'=>'required',
            'name_UA'=>'required','post_price'=>'required','image'=>'required'
        ,'description'=>'required'];
        return $this->fromPostToModel($rules, new Category, $request);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $rules = ['name_RU'=>'required','name_EN'=>'required',
            'name_UA'=>'required','post_price'=>'required','description'=>'required'];
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
