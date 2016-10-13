<?php

namespace App\Http\Controllers;

use App\Category;
use App\Event;
use App\Event_follow;
use App\Photo;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;
use Storage;

class EventsController extends Controller
{
    use SoftDeletes;

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
        return $this->helpReturn(Event::with('photos')->with('user')->get());
    }

    public function show($id) {
        //return $this->helpReturn(Event::find(2666));
        return $this->helpReturn(Event::with('photos', 'user')->findorfail($id));
    }

    /**
     * @api {get} /v1/users/events/follow getFollowEventsByUser
     * @apiVersion 0.1.0
     * @apiName getFollowEventsByUser
     * @apiGroup Events
     *
     * @apiDescription Получить события на которых пользователь нажал ИДУ
     * @apiHeader {string} token User token
     *
     */
    public function followsEvents(Request $request) {
        return $this->helpReturn($request->user->followsEvents);
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
            foreach (
                Event::with('photos', 'user')
                    ->where('category_id', $category->category_id)
                    //->where('type', 'public')
                    ->where('publish',1)
                    ->get() as $event) {
                        $events[] = $event;
            }
        }
        return $this->helpReturn($events);
    }

    /**
     * @api {get} /v1/events/byuser/:id getEventsByUser
     * @apiVersion 0.1.0
     * @apiName getEventsByUser
     * @apiGroup Events
     *
     * @apiDescription Все события из категорий на которые подписан пользователь
     * @apiHeader {string} token User token
     *
     */
    public function showByUser(Request $request, $id) {
        return $this->helpReturn(User::findorfail($id)->events);
    }


    protected function deleteDuplicateInFollows() {
        $exist = [];
        foreach (Event_follow::all() as $follow) {
            if(in_array($follow->event_id . ':' . $follow->user_id, $exist)) {
                $follow->delete();
            } else {
                $exist [] = $follow->event_id . ':' . $follow->user_id;
            }
        }
        return;
    }

    /**
     * @api {post} /v1/users/events/follow followEvents
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
        $this->deleteDuplicateInFollows();
        $valid = Validator($request->all(), ['event_id' => 'required']);
        if(!$valid->fails()) {
            if(!Event_follow::where('user_id', $request->user->id)->where('event_id', $request->event_id)->first()) {
                $follow = new Event_follow;
                $follow->user_id = $request->user->id;
                $follow->event_id = $request->event_id;
                $follow->save();
                return $this->helpInfo($follow);
            } else {
                return $this->helpInfo('Record already exists');
            }
        } else {
            return $this->helpError('valid', $valid);
        }
    }

    public function publish(Request $request, $id) {
        $event = Event::findorfail($id);
        if($event) {
            $event->publish = 1;
            $event->save();
            $this->helpInfo();
        } else {
            return $this->helpError('Event not found');
        }
    }

    public function unpublish(Request $request, $id) {
        $event = Event::findorfail($id);
        if($event) {
            $event->publish = 0;
            $event->save();
            $this->helpInfo();
        } else {
            return $this->helpError('Event not found');
        }
    }

    /**
     * @api {post} /v1/users/events/unfollow unfollowEvents
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
        if(!$valid->fails()) {
            $follow = Event_follow::where('event_id', $request->event_id)->where('user_id', $request->user->id)->delete();
            return $this->helpInfo();
        } else {
            return $this->helpError('valid', $valid);
        }
    }


    public function create() {
        //
    }

    /**
     * @api {post} /v1/events storeEvents
     * @apiVersion 0.1.0
     * @apiName storeEvents
     * @apiGroup Events
     * @apiDescription add Event object
     *
     * @apiHeader {string} token User token
     * @apiParam {string} category_id
     * @apiParam {string} title
     * @apiParam {string} description
     * @apiParam {string} date
     * @apiParam {string} time
     * @apiParam {string='private','public'} type
     * @apiParam {string} price
     * @apiParam {array} images
     * @apiParam {string} [place_id]
     * @apiParam {string} [address]
     *
     * @apiParam {datetime} date_stop Дата окончания показа в приложении
     *
     */
    public function store_save(Request $request) {
        //dump('Files');
        //dump($request->allFiles());
        //dump('All params');
        //dd($request->all());
        $rules = [
            'video' => false,
            'user_id' => false,
            'date_stop' => false,
            'address' => false,
            'place_id' => false,
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'date' => 'required',
            'time' => 'required',
            'type' => 'required',
            'price' => 'required',
            'images' => false,
        ];
        if($request->date_stop){
            $request->date_stop = new \DateTime($request->date_stop);
        }
        //date("Y-m-d H:i:s")
        if($request->date_stop < new \DateTime("now")) {
            throw new Exception('Date are wrong or less that:'.date("Y-m-d H:i:s"), 100);
        }

        $category = Category::findorfail($request->category_id);
        if($request->user->balance > $category->post_price) {
            $request->user->balance = $request->user->balance - $category->post_price;

            if($request->user_id AND $request->header('token') == 'adm'){
                User::findorfail($request->user_id);
            }else{
                $request->user_id = $request->user->id;
            }



            $event = $this->fromPostToModel($rules, new Event, $request, 'model');
            //dd(get_class($event));
            if(get_class($event) == 'App\Event') {
                $request->user->save();
                $users = [];
                if($event->type == 'public') {
                    foreach (User::all() as $user) {
                        $users[] = $user;
                    }
                    $message = [
                        'id' => $event->id,
                        'title' => $event->title,
                        'body' => $event->description,
                        'image' => $event->load('photos'),
                        'type' => 'EVENT_WAS_ADDED',
                        'creator_info' => User::find($request->user->id),
                    ];
                    $this->sendPushToUser($users, $message);
                    //dump(1);
                    //$job = new SendPush($users,$message);
                    //$this->dispatch($job);
                }
                return $this->helpReturn($request->user, false, $event->id);
            } else {
                return $this->helpError('valid', $event);
            }
        } else {
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

    public function edit($id) {
        return $this->getSchemaByModel(Event::withoutGlobalScopes()->first());
    }

    /**
     * @api {post} /v1/events/:id updateEvents
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
     * @apiParam {string} [place_id]
     * @apiParam {string} [address]
     *
     * @apiParam {datetime} date_stop Дата окончания показа в приложении
     *
     */
    public function update_save(Request $request, $id) {
        $rules = [
            'video' => false,
            'user_id' => false,
            'date_stop' => false,
            'address' => false,
            'place_id' => false,
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'date' => 'required',
            'time' => 'required',
            'type' => 'required',
            'price' => 'required',
            'images' => false,
        ];
        if($request->date_stop < date("Y-m-d H:i:s")) {
            throw new Exception('Date are wrong', 100);
        }
        $request->user_id = $request->user->id;
        if($request->images) {
            Photo::where('event_id', $id)->delete();
        }

        $event = $this->fromPostToModel($rules, Event::findorfail($id), $request, 'model');
        //dd(get_class($event));
        if(get_class($event) == 'App\Event') {
            $request->user->save();
            return $this->helpInfo($event->id);
        } else {
            return $this->helpError('valid', $event);
        }

    }

    public function getImagesFromEvent(Request $request, $id) {
        return $this->helpReturn(Event::findorfail($id)->photos());
    }

    public function removePhoto(Request $request, $id) {
        $photo = Photo::findorfail($id);
        Storage::delete(storage_path() . '/app/public/images', $photo->image);
        $photo->delete();
        return $this->helpInfo();
    }

    public function destroy($id) {
        Event::where('id', $id)->delete();
        return $this->helpInfo();
    }
}
