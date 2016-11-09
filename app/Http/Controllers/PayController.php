<?php

namespace App\Http\Controllers;

use App\Helpers\LiqPay;
use App\Invoice;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Log;
use App\Helpers;

class PayController extends Controller
{
    public function callback(Request $request){
        Log::info('Callback info');
        foreach ($request->all() as $key => $value) {
            Log::info($key.':'.$value);
        }
        $signature = $request->signature;
        $data = $request->data;
        /**
         * TODO: more security in future, but not now
         */
        //$liqpay = new LiqPay(env('LIQPAY_PUBLIC_KEY'), env('LIQPAY_PRIVATE_KEY'));
        //$dataCheck = $liqpay->cnb_data($data);
        //if($signature == $dataCheck['signature']){
            $data = base64_decode($data);
        $data = json_decode($data);
        $data = (array) $data;
        $invoice = Invoice::where('order_id',$data['order_id'])->first();
            if($invoice){
                $user = User::find($invoice->user_id);
                if($user){
                    $user->balance = $user->balance + $invoice->amount;
                    $user->save();
                }
            }

        //}else{
        //    Log::warning('wrong signature');
        //}
        return $this->helpInfo();
    }

    public function pay(Request $request,LiqPay $liqPay){
        $res = $liqPay->api("request", array(
            'action'         => 'pay',
            'version'        => '3',
            'phone'          => '380950000001',
            'amount'         => '0.01',
            'currency'       => 'USD',
            'description'    => 'description text', 
            'order_id'       => 'order_id_1',
            'card'           => $request->card,
            'card_exp_month' => $request->card_month,
            'card_exp_year'  => $request->yeah,
            'card_cvv'       => $request->cvv,
            'sandbox'        => '1',
            'public_key'     =>'i86486134497',
            'ip'             =>'192.168.0.1'
        ));
        return $this->helpReturn($res);
    }
    /**
     * @api {get} /v1/invoice storeInvoice
     * @apiVersion 0.1.0
     * @apiName storeInvoice
     * @apiGroup Invoices
     *
     * @apiHeader {string} token User token
     * @apiParam {array} data
     */
    public function invoice(Request $request){
        if($request->debug){
            dump(env('LIQPAY_PUBLIC_KEY'));
            dump(env('LIQPAY_PRIVATE_KEY'));
            dump(Input::all());
            dd($request->all());
        }
        $order_id = sha1(md5(uniqid()));
        if(is_array($request->data)){

            if(!isset($request->data['amount'])){
                throw new \Exception('amount in array was missing',100);
            }
            $data = $request->data;
            $data['server_url'] = 'http://posterapp.com.ua/api/payment/callback';
            $data['order_id'] = $order_id;
            /*
            $data['public_key'] = env('LIQPAY_PUBLIC_KEY');
            $data['order_id'] = $order_id;
            $data = base64_encode(json_encode($data));
            $signature = base64_encode(
                sha1(
                    env('LIQPAY_PRIVATE_KEY').$data.env('LIQPAY_PRIVATE_KEY')
                )
            );*/
            $liqpay = new LiqPay(env('LIQPAY_PUBLIC_KEY'), env('LIQPAY_PRIVATE_KEY'));

            $data = $liqpay->cnb_data($data);

            $invoice = new Invoice([
                'amount'=>$request->data['amount'],
                'order_id'=>$order_id,
                'user_id'=>$request->user->id
            ]);
            $invoice->save();
            return $this->helpReturn($data);
            //return $this->helpReturn(['base64data'=>$data,'signature'=>$signature]);
        }else{
            throw new \Exception('Param data is not array',100);
        }




    }
}
