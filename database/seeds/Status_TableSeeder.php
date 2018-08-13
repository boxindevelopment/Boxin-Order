<?php

use App\Entities\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Status_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('status')->insert(['name' => 'On the way to you']);
        DB::table('status')->insert(['name' => 'Upcoming']);
        DB::table('status')->insert(['name' => 'Stored']);
        DB::table('status')->insert(['name' => 'Success']);
        DB::table('status')->insert(['name' => 'Failed']);
        DB::table('status')->insert(['name' => 'Approved']);
        DB::table('status')->insert(['name' => 'Rejected']);
        DB::table('status')->insert(['name' => 'Fill']);
        DB::table('status')->insert(['name' => 'Empty']);
        DB::table('status')->insert(['name' => 'Pending']);
        DB::table('status')->insert(['name' => 'Finished']);
    }
}
