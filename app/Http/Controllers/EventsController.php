<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use App\User;
use App\Event;
use App\Event_follow;
use App\Http\Requests;
use Illuminate\Validation\Validator;
use Mockery\CountValidator\Exception;

class EventsController extends Controller
{
    /**
     * @api {get} /v1/events/:id getEvents
     * @apiVersion 0.1.0
     * @apiName getEvents
     * @apiGroup Events
     *
     * @apiParam {string} [id]
     *
     */
    public function index() {
        return $this->helpReturn(Event::with('photos')->get());
    }

    public function show($id) {
        return $this->helpReturn(Event::with('photos')->findorfail($id));
    }


    /**
     * @api {get} /v1/users/events/favorite getEventsByFavoriteCategories
     * @apiVersion 0.1.0
     * @apiName getEventsByFavoriteCategories
     * @apiGroup Events
     *
     * @apiDescription Все события из категорий на которые подписан пользователь
     * @apiHeader {string} token User token
     *
     */
    public function showFavorite(Request $request) {
        $categories = $request->user->favorites;
        $events = [];
        foreach ($categories as $category) {
            foreach (Event::where('category_id', $category->category_id)->where('type','public')->get() as $event) {
                $events[] = $event;
            }
        }
        return $this->helpReturn($events);
    }

    /**
     * @api {post} /v1/events/follow followEvents
     * @apiVersion 0.1.0
     * @apiName followEvents
     * @apiGroup Events
     *
     * @apiDescription Иду на событие
     * @apiHeader {string} token User token
     * @apiParam {integer} event_id
     *
     */
    public function follow(Request $request) {
        $valid = Validator($request->all(), ['event_id' => 'required']);
        if (!$valid->fails()) {
            $follow = new Event_follow;
            $follow->user_id = $request->user->id;
            $follow->event_id = $request->event_id;
            $follow->save();
            return $this->helpInfo();
        } else {
            return $this->helpError('valid', $valid);
        }
    }

    /**
     * @api {post} /v1/events/unfollow unfollowEvents
     * @apiVersion 0.1.0
     * @apiName unfollowEvents
     * @apiGroup Events
     *
     * @apiDescription НЕ иду на событие
     * @apiHeader {string} token User token
     * @apiParam {integer} event_id
     *
     */
    public function unfollow(Request $request) {
        $valid = Validator($request->all(), ['event_id' => 'required']);
        if (!$valid->fails()) {
            $follow = Event_follow::findorfail($request->event_id);
            $follow->delete();
            return $this->helpInfo();
        } else {
            return $this->helpError('valid', $valid);
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

    /**
     * @api {post} /v1/events storeEvents
     * @apiVersion 0.1.0
     * @apiName storeEvents
     * @apiGroup Events
     *
     * @apiHeader {string} token User token
     * @apiParam {string} category_id
     * @apiParam {string} title
     * @apiParam {string} description
     * @apiParam {string} date
     * @apiParam {string} time
     * @apiParam {string='private','public'} type
     * @apiParam {string} price
     *
     */
    public function store_save(Request $request) {
        $rules = ['category_id' => 'required', 'title' => 'required', 'description' => 'required', 'date' => 'required', 'time' => 'required', 'type' => 'required', 'price' => 'required'];
        $category = Category::findorfail($request->category_id);
        if($request->user->balance > $category->post_price){
            $request->user->balance = $request->user->balance - $category->post_price;
            $event = $this->fromPostToModel($rules, new Event, $request,true);
            if($event == true){
                $request->user->save();
                return $this->helpInfo();
            }else{
                return $this->helpError('valid',$event);
            }
        }else{
            return $this->helpError('dont have money');
        }
        /*
        $valid = Validator($request->all(), $rules);
        if (!$valid->fails()) {
            $event = new Event;
            $event->category_id = $request->category_id;
            $event->user_id = $request->user->id;
            $event->title = $request->title;
            $event->description = $request->description;
            $event->date = $request->date;
            $event->time = $request->time;
            $event->type = $request->type;
            $event->price = $request->price;
            if ($request->hasFile('image')) {
                $fileName = md5(rand(999, 9999) . date('d m Y')) . '.jpg';
                $request->file('image')->move(storage_path() . '/app/public/', $fileName);
                $event->image = $fileName;
            }
            $event->save();
            return $this->helpInfo();
        } else {
            $this->helpError('valid', $valid);
        }
        */
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
     * @api {put} /v1/events/:id updateEvents
     * @apiVersion 0.1.0
     * @apiName updateEvents
     * @apiGroup Events
     *
     * @apiHeader {string} token User token
     *
     * @apiParam {integer} id
     * @apiParam {string} category_id
     * @apiParam {string} title
     * @apiParam {string} description
     * @apiParam {string} date
     * @apiParam {string} time
     * @apiParam {string} type
     * @apiParam {string} price
     *
     */
    public function update_save(Request $request, $id) {
        $rules = ['category_id' => 'required', 'title' => 'required', 'description' => 'required', 'date' => 'required', 'time' => 'required', 'type' => 'required', 'price' => 'required'];
        $valid = Validator($request->all(), $rules);
        if (!$valid->fails()) {
            $event = Event::findorfail($id);
            $event->category_id = $request->category_id;
            $event->user_id = $request->user->id;
            $event->title = $request->title;
            $event->description = $request->description;
            $event->date = $request->date;
            $event->time = $request->time;
            $event->type = $request->type;
            $event->price = $request->price;
            if ($request->hasFile('image')) {
                $fileName = md5(rand(999, 9999) . date('d m Y')) . '.jpg';
                $request->file('image')->move(storage_path() . '/app/public/', $fileName);
                $event->image = $fileName;
            }
            $event->save();
            return $this->helpInfo();
        } else {
            $this->helpError('valid', $valid);
        }
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
