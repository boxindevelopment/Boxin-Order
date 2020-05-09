<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    protected $repository;

    public function __construct()
    {
    }

    public function confirmPayment(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'user_id'           => 'required',
            'status_id'         => 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/confirm-payment/' . $request->user_id, ['form_params' => [
        'status_id'       => $request->status_id,
        'order_detail_id' => $request->order_detail_id
        ]]);

        return response()->json([
            'status' => true,
            'message' => $response
        ]);

    }

    public function terminate(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'terminate_id'           => 'required',
            'status_id'         => 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/terminate/' . $request->terminate_id, ['form_params' => [
        'status_id'       => $request->status_id,
        'order_detail_id' => $request->order_detail_id
        ]]);

        return response()->json([
            'status' => true,
            'message' => $response
        ]);

    }

    public function take(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'take_id'           => 'required',
            'status_id'         => 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/take/' . $request->take_id, ['form_params' => [
        'status_id'       => $request->status_id,
        'order_detail_id' => $request->order_detail_id
        ]]);

        return response()->json([
            'status' => true,
            'message' => $response
        ]);

    }

    public function extend(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'extend_order_id'   => 'required',
            'status_id'         => 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/extend', ['form_params' => [
        'status_id'       => $request->status_id,
        'order_detail_id' => $request->order_detail_id,
        'extend_order_id' => $request->extend_order_id
        ]]);

        return response()->json([
            'status' => true,
            'message' => $response
        ]);

    }

    public function backwarehouse(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'return_id'         => 'required',
            'status_id'         => 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('APP_NOTIF') . 'api/backwarehouse/' . $request->return_id, ['form_params' => [
        'status_id'       => $request->status_id,
        'order_detail_id' => $request->order_detail_id
        ]]);

        return response()->json([
            'status' => true,
            'message' => $response
        ]);

    }

}
