<?php

namespace App\Http\Controllers;

use App\Helpers\LiqPay;
use Illuminate\Http\Request;
use App\Http\Requests;

class PayController extends Controller
{
    public function callback(Request $request){
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
}
