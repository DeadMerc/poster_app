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
                if($key == 'image' || $key == 'images') {
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
                    if($image) {
                        $fileName = md5(rand(999, 99999) . date('d m Y')) . '.jpg';
                        $image->move(storage_path() . '/app/public/images', $fileName);
                        $photo = new Photo;
                        $photo->event_id = $model->id;
                        $photo->image = $fileName;
                        $photo->save();
                        unset($photo);
                    }
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
        $protected = ['social_hash', 'token', 'created_at', 'updated_at', 'id', 'banned', 'imei'];
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
            //return 'categories_select';
        }
        if($key == 'image') {
            return 'file';
        }
        if($key == 'balance') {
            //return 'number';
        }
        if($key == 'date') {
            //return 'date';
        }
        if($key == 'publish') {
            return 'checkbox';
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
        if(!$response) {
            $arrayForResponse['error'] = true;
            $arrayForResponse['message'] = 'Resource not found';
        }

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
            $arrayForResponse['message'] = 'Resource not found';
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
            'Authorization: key=AIzaSyC058Lyrn0NKvEswoGBEP5Y7iNkj8edgss',
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
        $res = json_decode($result);
        return $res;
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
                'type'  => $message['type'],
                'alert' => [
                    'id'    => $message['id'],
                    'title' => $message['title'],
                    'body'  => $message['body'],
                    'image' => $message['image'],

                ],
                'badge' => '0',
                'sound' => 'default',
            ];
        }


        $tHost = 'gateway.sandbox.push.apple.com';
        $tPort = 2195;
        $errors = false;
        $tCert = storage_path() . '/app/dist.pem';
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
        return $tResult;
    }

    /*
     * @param user user collection
     * @param message array=message,image
     */

    public function sendPushToUser($user, $message) {

        if($user->device_type == 'android') {
            $response = $this->sendPushToAndroid(array($user->device_token), $message);
        } elseif($user->device_type == 'ios') {
            $response = $this->sendPushToIos(array($user->device_token), $message);
        } else {
            $response = false;
        }
        $push_history = new Push;
        $push_history->title = $message['title'];
        $push_history->description = $message['body'];
        $push_history->created_by = 'admin';
        $push_history->send_to = $user->id;
        $push_history->image = $message['image'];
        $push_history->type = $message['type'];
        $push_history->save();
        $response = 'Ok';
        //$response = $user->id . ':' . $response;
        return $response;
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
