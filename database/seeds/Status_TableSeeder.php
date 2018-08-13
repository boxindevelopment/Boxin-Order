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
            ['id' => 1, 'name' => 'On the way to you'],
            ['id' => 2, 'name' => 'Upcoming'],
            ['id' => 3, 'name' => 'Stored'],
            ['id' => 4, 'name' => 'Success'],
            ['id' => 5, 'name' => 'Failed'],
            ['id' => 6, 'name' => 'Approved'],
            ['id' => 7, 'name' => 'Rejected'],
            ['id' => 8, 'name' => 'Fill'],
            ['id' => 9, 'name' => 'Empty'],
            ['id' => 10, 'name' => 'Pending'],
            ['id' => 11, 'name' => 'Finished'],
        ]);
    }
}
