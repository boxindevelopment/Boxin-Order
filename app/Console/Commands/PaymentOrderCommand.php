<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PaymentOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check & update status payment order midtrans';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
