<?php

namespace App\Jobs;

use App\Model\Order;
use App\Model\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use PDF;

class MessageInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $pdf = PDF::loadView('pdf.invoice', ['order' => $this->order]);
        $pdf->setPaper(array(0, 0, 250, 400), 'portrait');
        $pdf->save('invoice.pdf');
        $message = 'Ok es';
        $users = 1;
        $orders = ['data' => 'Test ok'];
        // $orders = $this->order;
        // Mail::send('emails.invoice', $orders, function($message) use($users) {
        //     $message->to('bahtiar@twiscode.com');
        //     $message->subject('Test Email');
        //     $message->attach('invoice.pdf');
        // });

    }
}
