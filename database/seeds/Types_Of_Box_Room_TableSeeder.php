<?php

use Illuminate\Database\Seeder;

class Types_Of_Box_Room_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types_of_box_room')->insert([
            ['name' => 'box'],
            ['name' => 'room'],
        ]);
    }
}
