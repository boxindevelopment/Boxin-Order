<?php

use App\Entities\Status;
use Illuminate\Database\Seeder;

class Status_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('status')->insert([
            ['name' => 'On the way to you'],
            ['name' => 'Upcoming'],
            ['name' => 'Stored'],
            ['name' => 'Success'],
            ['name' => 'Failed'],
            ['name' => 'Approved'],
            ['name' => 'Rejected'],
            ['name' => 'Fill'],
            ['name' => 'Empty'],
            ['name' => 'Pending'],
            ['name' => 'Finished'],
        ]);
    }
}
