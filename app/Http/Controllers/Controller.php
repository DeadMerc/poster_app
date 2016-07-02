<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function fromPostToModel($rules, $model, $request, $bool = false) {
        $rulesForValidator = [];
        foreach ($rules as $key => $value) {
            if ($value !== false) {
                $rulesForValidator[$key] = $value;
            }
        }
        $valid = Validator($request->all(), $rulesForValidator);
        $manyImages = false;
        if (!$valid->fails()) {
            foreach ($rules as $key => $value) {
                if ($key == 'image' || $key == 'images') {
                    if (is_array($request->images)) {
                        $manyImages = true;
                    } else {
                        if ($request->hasFile('image')) {
                            $fileName = md5(rand(9999, 99999) . date('d m Y') . rand(9999, 99999)) . '.jpg';
                            $request->file('image')->move(storage_path() . '/app/public/images', $fileName);
                            $model->image = $fileName;
                        }elseif($request->image){
                            $model->image = $request->image;
                        } else {
                            $model->image = null;
                        }
                    }
                } else {
                    $model->$key = $request->$key;
                }

            }
            $model->save();

            if ($manyImages) {
                foreach ($request->images as $image) {
                    if ($image) {
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

            if ($bool) {
                return true;
            }
            return $this->helpInfo();
        } else {
            if ($bool) {
                return $valid->errors()->all();
            }
            return $this->helpError('valid', $valid);
        }
    }

    public function getSchemaByModel($model) {
        $attributes = $model->getAttributes();
        $keys = [];
        $protected = ['social_hash', 'token', 'created_at', 'updated_at', 'type', 'id', 'banned', 'imei'];
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $protected)) {
                $keys[] = array('type' => $this->getTypeInputByKey($key), 'key' => $key);
            }
        }
        return $this->helpReturn($keys);
    }

    private function getTypeInputByKey($key) {
        if ($key == 'category_id') {
            //return 'categories_select';
        }
        if ($key == 'image') {
            return 'file';
        }
        if ($key == 'balance') {
            //return 'number';
        }
        return 'text';
    }

    public function helpError($message = 'valid', $validator = false) {

        if ($validator) {
            return array('response' => [], 'error' => true, 'message' => 'valid', 'validator' => $validator->errors()->all());
        }
        return array('response' => [], 'error' => true, 'message' => $message);
    }

    public function helpReturn($response, $info = false, $message = false) {
        $arrayForResponse['response'] = $response;
        if ($info) {
            $arrayForResponse['info'] = $info;
        }
        if ($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['error'] = false;
        if (!$response) {
            $arrayForResponse['error'] = true;
            $arrayForResponse['message'] = 'Resource not found';
        }

        return $arrayForResponse;
    }

    public static function helpReturnS($response, $info = false, $message = false) {
        $arrayForResponse['response'] = $response;
        if ($info) {
            $arrayForResponse['info'] = $info;
        }
        if ($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['error'] = false;
        if (!$response) {
            $arrayForResponse['error'] = true;
            $arrayForResponse['message'] = 'Resource not found';
        }

        return $arrayForResponse;
    }

    public function helpInfo($message = false) {
        if ($message) {
            $arrayForResponse['message'] = $message;
        }
        $arrayForResponse['response'] = [];
        $arrayForResponse['error'] = false;
        return $arrayForResponse;
    }

    /**
     * @device_ids string sa
     * @message arrray message,type,id
     */
    public function sendPushToAndroid(array $device_ids, $message = false) {
        if (!$message) {
            $message = array('message' => 'here is a message. message', 'title' => 'This is a title. title', 'subtitle' => 'This is a subtitle. subtitle', 'tickerText' => 'Ticker text here...Ticker text here...Ticker text here', 'vibrate' => 1, 'sound' => 1, 'largeIcon' => 'large_icon', 'smallIcon' => 'small_icon');
        }

        $fields = array('registration_ids' => $device_ids, 'data' => $message);
        $headers = array('Authorization: key=AIzaSyCJb8kzYjf6vTu1gyet0ZS_4v4MoiaqVEA', 'Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        //print_r($result);
        curl_close($ch);
        return $result;
    }

    public function sendPushToIos($device_ids = false, $message = false) {
        $tHost = 'gateway.push.apple.com';
        $tPort = 2195;
        $errors = false;
        $tCert = storage_path() . '/app/cert.pem';
        $tPassphrase = '';
        //$tToken = '0a32cbcc8464ec05ac3389429813119b6febca1cd567939b2f54892cd1dcb134';
        $tToken = $device_ids;
        $tAlert = 'Alert';
        $tBadge = 8;
        $tSound = 'default';
        $tPayload = 'Payload';
        $tBody['aps'] = array('alert' => $tAlert, 'badge' => $tBadge, 'sound' => $tSound,);
        $tBody ['payload'] = $tPayload;
        $tBody = json_encode($tBody);
        $tContext = stream_context_create();
        stream_context_set_option($tContext, 'ssl', 'local_cert', $tCert);
        stream_context_set_option($tContext, 'ssl', 'passphrase', $tPassphrase);
        $tSocket = stream_socket_client('ssl://' . $tHost . ':' . $tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $tContext);
        if (!$tSocket)
            $errors = 'Cant open socket';
        $tMsg = chr(0) . chr(0) . chr(32) . pack('H*', $tToken) . pack('n', strlen($tBody)) . $tBody;
        $tResult = fwrite($tSocket, $tMsg, strlen($tMsg));
        if ($tResult)
            $errors = false; else
            $errors = $tResult;
        fclose($tSocket);
        return $errors;
    }

    /*
     * @param user user collection
     * @param message array=message,image
     */

    public function sendPushToUser($user, $message) {
        if ($user->deviceType == 'android') {
            $response = $this->sendPushToAndroid(array($user->deviceToken), $message);
        } elseif ($user->deviceType == 'ios') {
            $response = $this->sendPushToIos(array($user->deviceToken), $message);
        } else {
            $response = false;
        }
        return $response;
    }

    public function uploadFile(Request $request) {
        if ($request->hasFile('image')) {
            $fileName = md5(rand(9999, 99999) . date('d m Y') . rand(9999, 99999)) . '.jpg';
            $request->file('image')->move(storage_path() . '/app/public/images', $fileName);
            return $fileName;
        }
    }

}
