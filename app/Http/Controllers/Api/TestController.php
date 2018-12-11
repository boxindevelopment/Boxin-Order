<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;
use Mail;

class TestController extends Controller
{

    public function __construct()
    {
    }

    public function mail(Request $request)
    {
        // $order = Order::with('order_detail', 'pickup_order.order_detail_box', 'payment')->findOrFail($order->id);
        $order = ['data' => 'Meidina Istimewa Nurmala'];
        $pdf = PDF::loadView('pdf.invoice', $order);
        $pdf->setPaper(array(0, 0, 280, 400), 'portrait');
        $pdf->save('invoice.pdf');
        $message = 'Ok es';

        Mail::send('emails.invoice', $order, function($message) use($order) {
            $message->to('bahtiar@twiscode.com');
            // if($data['cc'] != null){
            //     $message->cc($data['cc']);
            // }
            $message->subject('Test Email');

            //Full path with the pdf name
            $message->attach('invoice.pdf');
        });

    }

}
