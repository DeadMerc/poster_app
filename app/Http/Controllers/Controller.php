<?php

namespace App\Http\Controllers;

use App\Push;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use App\Photo;
use App\Http\Requests;
use Log;
class Controller extends BaseController {
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function fromPostToModel($rules, $model, $request, $bool = false, $info = false) {
        $rulesForValidator = [];
        foreach($rules as $key => $value) {
            if($value !== false) {
                $rulesForValidator[ $key ] = $value;
            }
        }
        $valid = Validator($request->all(), $rulesForValidator);
        $manyImages = false;
        if(!$valid->fails()) {
            foreach($rules as $key => $value) {
                if($key == 'image' OR $key == 'images') {
                    if(is_array($request->images)) {
                        $manyImages = true;
                    } else {
                        if($request->hasFile('image')) {
                            $fileName = md5(rand(9999, 99999) . date('d m Y') . rand(9999, 99999)) . '.jpg';
                            $request->file('image')->move(storage_path() . '/app/public/images', $fileName);
                            $model->image = $fileName;
                        } elseif(!empty($request->image)) {
                            $model->image = $request->image;
                        }
                    }
                } else {
                    if($model->$key != $request->$key AND !empty($request->$key)) {
                        if($key == 'password') {
                            $request->$key = md5($request->$key . 'requestLoginEvstolia');
                        }
                        $model->$key = $request->$key;
                    }
                }

            }
            $model->save();

            if($manyImages) {
                foreach($request->images as $image) {
                    if(is_string($image)){
                        $fileName = $image;
                    }else{
                        $fileName = md5(rand(999, 99999) . date('d m Y')) . '.jpg';
                        $image->move(storage_path() . '/app/public/images', $fileName);

                    }
                    $photo = new Photo;
                    $photo->event_id = $model->id;
                    $photo->image = $fileName;
                    $photo->save();
                    unset($photo);
                }
            }
            if($bool == 'model') {
                return $model;
            }
            if($bool) {
                return true;
            }
            return $this->helpReturn($model, $info);
        } else {
            if($bool) {
                return $valid;
            }
            return $this->helpError('valid', $valid);
        }
    }

    public function getSchemaByModel($model, $moreProtected = false) {
        $attributes = $model->getAttributes();
        $keys = [];
        $protected = ['deleted_at','social_hash', 'token', 'created_at', 'updated_at', 'id', 'banned', 'imei'];
        foreach($attributes as $key => $value) {
            if(!in_array($key, $protected)) {
                //защита специфических полей
                if(is_array($moreProtected)) {
                    if(!in_array($key, $moreProtected)) {
                        $keys[] = array('type' => $this->getTypeInputByKey($key), 'key' => $key);
                    }
                } else {
                    $keys[] = array('type' => $this->getTypeInputByKey($key), 'key' => $key);
                }
            }
        }
        return $this->helpReturn($keys);
    }

    private function getTypeInputByKey($key) {
        if($key == 'category_id') {
            return 'categories_select';
        }
        if($key == 'image') {
            return 'file';
        }
        if($key == 'balance') {
            //return 'number';
        }
        if($key == 'date') {
            return 'date_picker';
        }
        if($key == 'publish') {
            return 'checkbox';
        }
        if($key == 'type'){
            //return 'select_event';
        }
        if($key == 'date_stop'){
            return 'date_picker';
        }
        return 'text';
    }

    public function helpError($message = 'valid', $validator = false) {

        if($validator) {
            return array(
                'response'  => [],
                'error'     => true,
                'message'   => 'valid',
                'validator' => $validator->errors()->all(),
            );
        }
        return array('response' => [], 'error' => true, 'message' => $message);
    }

    public function helpReturn($response, $info = false, $message = false) {
        $arrayForResponse['response'] = $response;
        if($info) {
            $arrayForResponse['info'] = $info;
        }
        if($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['error'] = false;
        /*
        if(!$response) {
            $arrayForResponse['error'] = true;
            $arrayForResponse['message'] = 'Resource not found';
        }*/

        return $arrayForResponse;
    }

    public static function helpReturnS($response, $info = false, $message = false) {
        $arrayForResponse['response'] = $response;
        if($info) {
            $arrayForResponse['info'] = $info;
        }
        if($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['error'] = false;
        if(!$response) {
            $arrayForResponse['error'] = true;
        }

        return $arrayForResponse;
    }

    public function helpInfo($message = false) {
        if($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['response'] = [];
        $arrayForResponse['error'] = false;
        return $arrayForResponse;
    }

    /**
     * @device_ids string sa
     * @message array message,type,id
     */
    public function sendPushToAndroid(array $device_ids, $message = false) {
        //$device_ids = array($_GET['id']);
        $res = 'Failed';
        if(!$message) {
            $message = array(
                'message'    => 'here is a message. message',
                'title'      => 'This is a title. title',
                'subtitle'   => 'This is a subtitle. subtitle',
                'tickerText' => 'Ticker text here...Ticker text here...Ticker text here',
                'vibrate'    => 1,
                'sound'      => 1,
                'largeIcon'  => 'large_icon',
                'smallIcon'  => 'small_icon',
            );
        }

        $fields = array('registration_ids' => $device_ids, 'data' => $message);
        $headers = array(
            //'Authorization: key=AIzaSyCJb8kzYjf6vTu1gyet0ZS_4v4MoiaqVEA',
            'Authorization: key='.env('ANDROID_KEY','AIzaSyC058Lyrn0NKvEswoGBEP5Y7iNkj8edgss'),
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        //print_r($result);
        //dd(json_decode($result));
        curl_close($ch);
        //$res = json_decode($result);
        foreach ($device_ids as $id){
            Log::info('PUSH to ANDROID:'.$id);
        }

        return;
    }

    public function sendPushToIos($device_ids = false, $message = false) {

        if(!$message) {
            $message = [
                'category' => 'NEW_INVITE',
                'alert'    => [
                    'title' => 'test title',
                    'body'  => 'test body',
                ],
                'badge'    => '0',
                'sound'    => 'default',
            ];

        } else {
            $message = [
                "content-available" => 1,
                'type'  => $message['type'],
                'alert' => [
                    'id'    => $message['id'],
                    'title' => $message['title'],
                    'body'  => $message['body'],
                    'image' => $message['image'],
                    'link' => (!empty($message['link'])?$message['link']:null),
                    'date' => (!empty($message['date'])?$message['date']:null),
                    'creator_info' => (!empty($message['creator_info'])?$message['creator_info']:null)
                ],
                'badge' => '0',
                'sound' => 'default',
            ];
        }

        //.sandbox
        $tHost = env('IOS_PUSH_URL','gateway.push.apple.com');
        $tPort = 2195;
        $errors = false;
        $tCert = storage_path() . '/app/'.env("IOS_CERT",'dist.pem');
        $tPassphrase = 'qwer';
        $tToken = $device_ids;
        //$tToken = '913102bc68b8cd1a65f2e5ebe39f4ccb0e03de5c25f107e14a9698d6d1be4d20';
        $tPayload = 'Payload';
        $error = '';
        $errstr = '';
        $tBody['aps'] = $message;
        $tBody ['payload'] = $tPayload;
        $tBody['acme2'] = ["bang", "whiz"];
        $tBody = json_encode($tBody);
        if(is_array($tToken)) {
            $tToken = $tToken[0];
        }
        //echo $tBody;
        $tContext = stream_context_create();
        stream_context_set_option($tContext, 'ssl', 'local_cert', $tCert);
        stream_context_set_option($tContext, 'ssl', 'passphrase', $tPassphrase);
        $tSocket = stream_socket_client('ssl://' . $tHost . ':' . $tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $tContext);
        if(!$tSocket)
            $errors = 'Cant open socket';

        $tMsg = chr(0) . chr(0) . chr(32) . pack('H*', $tToken) . pack('n', strlen($tBody)) . $tBody;
        $tResult = fwrite($tSocket, $tMsg, strlen($tMsg));
        /*dump($error);
        dump($errstr);
        dump($tResult);*/
        fclose($tSocket);
        Log::info('PUSH to IOS:'.$tToken);

        return $tResult;
    }

    /*
     * @param user user collection
     * @param message array=message,image
     */

    public function sendPushToUser(array $users, $message) {
        $androidTokensList = [];
        $iosTokensList = [];
        foreach ($users as $user){
            if($user->device_type == 'android'){
                $this->storeHistoryPush($message,$user);
                $androidTokensList[] = $user->device_token;
            }elseif($user->device_type == 'ios'){
                $this->storeHistoryPush($message,$user);
                $iosTokensList[] = $user->device_token;
            }
        }
        $this->sendPushToAndroid($androidTokensList, $message);
        foreach ($iosTokensList as $token){
            $this->sendPushToIos($token, $message);

        }
        $response = 'Ok';
        //$response = $user->id . ':' . $response;
        return $response;
    }

    protected function storeHistoryPush($message,$user){
        $push_history = new Push;
        $push_history->title = $message['title'];
        $push_history->description = $message['body'];
        $push_history->created_by = 'admin';
        $push_history->send_to = $user->id;
        $push_history->image = $message['image'];
        $push_history->type = $message['type'];
        $push_history->save();
    }

    public function uploadFile(Request $request) {
        if($request->hasFile('image')) {
            $fileName = md5(rand(9999, 99999) . date('d m Y') . rand(9999, 99999)) . '.jpg';
            $request->file('image')->move(storage_path() . '/app/public/images', $fileName);
            if(!empty($request->event)) {
                $photo = new Photo;
                $photo->event_id = $request->event;
                $photo->image = $fileName;
                $photo->save();
                return $this->helpInfo($photo);
            }
            return $fileName;
        }
    }

}
