<?php

namespace App\Http\Controllers;

use App\Push;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class PushController extends Controller {
    public function edit($id) {
        return $this->getSchemaByModel(Push::first());
    }

    public function index() {
        return $this->helpReturn(Push::with('user')->get());
    }

    public function sendForUser(Request $request, $id) {
        $info[] = $this->sendPushToUser(User::findorfail($id), [
                'id'    => 'PUSH TITLE hey',
                'title' => 'TEST TITLE',
                'body'  => 'PUSH DESC',
                'image' => 'PUSH IMAGE',
                'type'  => 'TEST TYPE',
            ]);
        return $info;
    }


    public function send(Request $request) {
        $rules = ['image' => 'required', 'title' => 'required', 'description' => 'required'];
        $valid = Validator($request->all(), $rules);
        if($valid->fails()) {
            //return $this->helpError('valid',$valid);
        }
        $data = $request->all();
        //print_r($data);
        $push = $data['push'];
        $users = $data['users'];
        $info = [];
        foreach($users as $user) {
            $push_history = new Push;
            $push_history->title = $push['title'];
            $push_history->description = $push['description'];
            $push_history->created_by = 'admin';
            $push_history->send_to = $user['id'];
            $push_history->image = $push['image'];
            $push_history->save();


            $info[] = $this->sendPushToUser(User::find($user['id']), [
                    'title'       => $push['title'],
                    'description' => $push['description'],
                    'image'       => $push['image'],
                ]);
        }
        return $this->helpInfo($info);
    }

    public function destroy(Request $request, $id) {
        $item = Push::findorfail($id);
        $item->delete();
        return $this->helpInfo();
    }

}
