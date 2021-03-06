<?php

use Illuminate\Database\Seeder;

class Types_Of_Duration_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types_of_duration')->insert([
            ['name' => 'Daily', 'alias' => 'day'],
            ['name' => 'Weekly', 'alias' => 'week'],
            ['name' => 'Monthly', 'alias' => 'month'],
        ]);
    }
}
