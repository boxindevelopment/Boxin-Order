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
            ['id' => 1, 'name' => 'Daily', 'alias' => 'day'],
            ['id' => 2, 'name' => 'Weekly', 'alias' => 'week'],
            ['id' => 3, 'name' => 'Monthly', 'alias' => 'month'],
        ]);
    }
}
