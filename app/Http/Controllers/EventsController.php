<?php

namespace App\Http\Controllers;

use App\Category;
use App\Comment;
use App\Event;
use App\Event_follow;
use App\EventCinemaUser;
use App\Photo;
use App\User;
use App\User_hidden_fields;
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
    public function index(Request $request) {
        return $this->helpReturn(Event::with('photos')->with('user')->orderBy('id', 'desc')->when($request->unpublish, function($q) use ($request) {
            if($request->unpublish == 'true') {
                return $q->where('publish', "0");
            } else {
                return $q;
            }
        })->whereHas('user', function($q) use ($request) {
            if($request->search) {
                $q->where('email', 'like', '%' . $request->search . '%');
            }
        })->when($request->search, function($q) use ($request) {
            $q->orWhere('title', 'like', '%' . $request->search . '%');
        })->paginate(($request->per_page ? $request->per_page : "50")));
        //return $this->helpReturn(Event::with('photos')->with('user')->get());
    }

    public function show($id) {
        //return $this->helpReturn(Event::find(2666));
        return $this->helpReturn(Event::with('photos', 'user', 'cinema')->findorfail($id));
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
     * @api {get} /v1/users/events/favorite/:place_id getEventsByFavoriteCategories
     * @apiVersion 0.1.0
     * @apiName getEventsByFavoriteCategories
     * @apiGroup Events
     *
     * @apiDescription Все события из категорий на которые подписан пользователь
     * @apiHeader {string} token User token
     * @apiParam {string} [place_id]
     */
    public function showFavorite(Request $request, $place_id = false) {
        //DB::enableQueryLog();
        //dd($categories);
        //$place_id = false;
        /*
        Listener::listen('illuminate.query', function($query, $params, $time, $conn)
        {
            dd(array($query, $params, $time, $conn));
        });
        $search = [];
        foreach ($categories as $category) {
            $search[] = $category->category_id;
        }*/
        $events = Event::with('photos', 'user', 'comments')->where('category_id', $request->category_id)//->where('type', 'public')
        ->where('publish', 1)->when($place_id, function($q) use ($place_id) {
            return $q->where('place_id', $place_id);
        })->paginate(7);
        //dd($events);
        //dd(DB::getQueryLog());
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
    /**
     * @api {post} /v1/events storeEvents
     * @apiVersion 0.1.1
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
     * @apiParam {string} [phone_1]
     * @apiParam {string} [phone_2]
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
            'phone_1' => false,
            'phone_2' => false,
            'publish' => false,
        ];

        if($request->category_id == '100' OR $request->user->type == 'admin') {
            $request->publish = 1;
        }

        if($request->images) {
            if(!is_array($request->images)) {
                $request->images = explode(',', $request->images);
            }
        }

        if($request->date) {
            if(strtotime($request->date)) {
                $request->date = new \DateTime($request->date);
            }

        }

        if($request->date_stop) {
            if(strtotime($request->date_stop)) {
                $request->date_stop = new \DateTime($request->date_stop);
            }
        }
        //date("Y-m-d H:i:s")
        if($request->date_stop < new \DateTime("now")) {
            throw new Exception('Date are wrong or less that:' . date("Y-m-d H:i:s"), 100);
        }
        $category = Category::findorfail($request->category_id);
        if($request->user->balance > $category->post_price) {
            $request->user->balance = $request->user->balance - $category->post_price;
            $this->saveOriginalUserId($request);
            $event = $this->fromPostToModel($rules, new Event, $request, 'model');
            //dd();
            //dd($event);
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
                        'creator_info' => User::find($request->user_id),
                    ];
                    //$this->sendPushToUser($users, $message);
                    //dump(1);
                    //$job = new SendPush($users,$message);
                    //$this->dispatch($job);
                }
                //dd($event);
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
            'date' => false,
            'time' => 'required',
            'type' => 'required',
            'price' => 'required',
            'images' => false,
            'publish' => false,
            'phone_1' => false,
            'phone_2' => false,

        ];
        if($request->category_id == '100' OR $request->user->type == 'admin') {
            $request->publish = 1;
        }

        if($request->images) {
            if(!is_array($request->images)) {
                $request->images = explode(',', $request->images);
            }
        }

        if($request->date) {
            if(strtotime($request->date)) {
                $request->date = new \DateTime($request->date);
            }
            if($request->user->type != 'admin') {
                $request->date = null;
            }
        }


        if(strtotime($request->date_stop)) {
            $request->date_stop = new \DateTime($request->date_stop);
        }

        if($request->date_stop < new \DateTime("now")) {
            throw new Exception('Date are wrong or less that:' . date("Y-m-d H:i:s"), 100);
        }

        $this->saveOriginalUserId($request);

        if($request->images) {
            Photo::where('event_id', $id)->delete();
        }

        $event = $this->fromPostToModel($rules, Event::findorfail($id), $request, 'model');
        if($request->cinema) {
            EventCinemaUser::where('event_id', $id)->delete();
            $cinemas = json_decode($request->cinema);
            foreach ($cinemas as $cinema) {
                foreach ($cinema->sessions as $session) {
                    $eventCinema = new EventCinemaUser;
                    $eventCinema->event_id = $id;
                    $eventCinema->user_id = $cinema->id;
                    $eventCinema->price = $session->price;
                    $eventCinema->date = $session->date;
                    $eventCinema->save();
                }
            }

        }

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

    private function saveOriginalUserId(Request $request) {
        if($request->user_id AND $request->header('token') == 'adm') {
            User::findorfail($request->user_id);
        } else {
            $request->user_id = $request->user->id;
        }
    }

    /**
     * @api {post} /v1/events/:id/comment commentEvent
     * @apiVersion 0.1.0
     * @apiName commentEvent
     * @apiGroup Events
     *
     * @apiHeader {string} token User token
     *
     * @apiParam {string} body Max 3k symbols
     *
     *
     */
    public function comment(Request $request, $event_id) {
        $event = Event::findorfail($event_id);
        $comment = new Comment;
        $comment->event_id = $event_id;
        $comment->user_id = $request->user->id;
        $comment->body = $request->body;
        $comment->save();
        return $this->helpReturn($comment);
    }


    /**
     * @api {get} /v1/category/:id/users getPostedUsersInCategory
     * @apiVersion 0.1.0
     * @apiName getPostedUsersInCategory
     * @apiGroup Events
     * @apiDescription Получаем пользователей вместо эвентов в категории ( кинотеатр )
     *
     * @apiHeader {string} token User token
     *
     *
     */
    public function getPostedUsersInCategory(Request $request, $category_id) {
        $category = Category::findorfail($category_id);
        $users = User::
        join('events', 'events.user_id', '=', 'users.id')->where('events.category_id', $category_id)->select('users.name', 'users.image', 'users.email', 'users.id')->get();
        $users = $users->unique();
        return $this->helpReturn($users->values()->all());
    }

    /**
     * @api {get} /v1/events/:id/cinema getEventForCinema
     * @apiVersion 0.1.0
     * @apiName getEventForCinema
     * @apiGroup Events
     *
     * @apiHeader {string} token User token
     *
     * @apiParam {date} [date] For example=2017-01-19
     *
     *
     */
    public function getCinemaByEvent(Request $request, $event_id) {
        //DB::enableQueryLog();
        $event = Event::
        with('comments', 'photos')->where('id', $event_id)/*
            ->with(['cinema' => function($query) use ($request){
                $query->when($request->date,function($q)use($request){
                    $q->where('date','>=',$request->date.' 00:00:00')
                        ->where('date','<=',$request->date.' 23:59:59');
                });
            }])*/
        ->first();
        $event = $event->toArray();
        $event['cinema'] = [];
        $sessions = EventCinemaUser::
        when($request->date, function($q) use ($request) {
            $q->where('date', '>=', $request->date . ' 00:00:00')->where('date', '<=', $request->date . ' 23:59:59');
        })->where('event_id', $event_id)->get();
        foreach ($sessions as $session) {
            $cinemaFound = false;
            for ($i = 0; $i < count($event['cinema']); $i++) {
                if($session->user_id == $event['cinema'][$i]['id']) {
                    $event['cinema'][$i]['sessions'][] = $session->toArray();
                    $cinemaFound = true;
                }
            }
            if($cinemaFound == false) {
                $cinemaNew = User_hidden_fields::find($session->user_id)->toArray();
                $cinemaNew['sessions'] = [$session];
                $event['cinema'][] = $cinemaNew;
            }
        }
        //dd($event['cinema']);
        //dd(DB::getQueryLog());
        return $this->helpReturn($event);
    }

    /**
     * @api {get} /v1/users/:id/sessions getSessionsForCinema
     * @apiVersion 0.1.0
     * @apiName getSessionsForCinema
     * @apiGroup Events
     *
     * @apiHeader {string} token User token
     *
     * @apiParam {date} [date] For example=2017-01-19
     *
     *
     */
    public function getSessionsByUser(Request $request, $user_id) {
        $cinema = User_hidden_fields::where('id', $user_id)->first();
        $cinema = $cinema->toArray();
        $event = $cinema;
        $event['event'] = [];
        foreach (EventCinemaUser::when($request->date, function($q) use ($request) {
            $q->where('date', '>=', $request->date . ' 00:00:00')->where('date', '<=', $request->date . ' 23:59:59');
        })->where('user_id', $user_id)->get() as $session) {
            $cinemaFound = false;
            for ($i = 0; $i < count($event['event']); $i++) {
                if($session->event_id == $event['event'][$i]['id']) {
                    $event['event'][$i]['sessions'][] = $session->toArray();
                    $cinemaFound = true;
                }
            }
            if($cinemaFound == false) {
                $cinemaNew = Event::with('photos')->find($session->event_id)->toArray();
                $cinemaNew['sessions'] = [$session];
                $event['event'][] = $cinemaNew;
            }
        }

        return $this->helpReturn($event);
    }
}
