<?php

namespace App\Http\Controllers;

use App\Push;
use App\User;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function edit($id) {
        return $this->getSchemaByModel(Push::first());
    }

    public function index(Request $request) {
        return $this->helpReturn(Push::with('user')->orderBy('id', 'desc')->skip($request->page * $request->per_page)->take($request->per_page)->get());
    }

    public function sendForUser(Request $request, $id) {
        $info[] = $this->sendPushToUser([User::findorfail($id)], [
            'id' => false,
            'title' => 'TITLE',
            'body' => 'BODY',
            'image' => 'IMAGE',
            'type' => (!empty($push['type']) ? $push['type'] : null),
            'date' => (!empty($push['date']) ? $push['date'] : null),
            'link' => (!empty($push['link']) ? $push['link'] : null)
        ]);
        return $info;
    }


    public function send(Request $request) {
        $rules = [
            'image' => 'required',
            'title' => 'required',
            'description' => 'required'
        ];
        $valid = Validator($request->all(), $rules);
        if($valid->fails()) {
            //return $this->helpError('valid',$valid);
        }
        $data = $request->all();

        //print_r($data);
        $push = $data['push'];
        //dump($push);dd();
        $users = $data['users'];
        $info = [];
        $userForPush = [];
        foreach ($users as $user) {
            /*$push_history = new Push;
            $push_history->title = $push['title'];
            $push_history->description = $pzush['description'];
            $push_history->created_by = 'admin';
            $push_history->send_to = $user['id'];
            $push_history->image = $push['image'];
            $push_history->save();*/
            $userForPush[] = User::find($user['id']);

        }
        $message = [
            'id' => false,
            'title' => $push['title'],
            'body' => $push['description'],
            'image' => $push['image'],
            'type' => (!empty($push['type']) ? $push['type'] : null),
            'date' => (!empty($push['date']) ? $push['date'] : null),
            'link' => (!empty($push['link']) ? $push['link'] : null)
        ];
        //$job = new SendPush($userForPush,$message);
        //$this->dispatch($job);
        $info = $this->sendPushToUser($userForPush, $message);
        /*
        $info = $this->sendPushToUser($userForPush, [
            'id' => false,
            'title' => $push['title'],
            'body' => $push['description'],
            'image' => $push['image'],
            'type' => (!empty($push['type'])?$push['type']:null),
            'date' => (!empty($push['date'])?$push['date']:null),
            'link' => (!empty($push['link'])?$push['link']:null)
        ]);*/
        return $this->helpInfo($info);
    }

    public function destroy(Request $request, $id) {
        $item = Push::findorfail($id);
        $item->delete();
        return $this->helpInfo();
    }

}
