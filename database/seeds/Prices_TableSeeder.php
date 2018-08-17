<?php

use Illuminate\Database\Seeder;

class Prices_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('prices')->insert([
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 1, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 1, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 1, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 2, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 2, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 2, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 3, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 3, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 1, 
                'types_of_size_id' => 3, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 4, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 4, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 4, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 5, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 5, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 5, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 6, 
                'types_of_duration_id' => 1, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 6, 
                'types_of_duration_id' => 2, 
                'price' => 0,
            ],
            [
                'types_of_box_room_id' => 2, 
                'types_of_size_id' => 6, 
                'types_of_duration_id' => 3, 
                'price' => 0,
            ],
            
        ]);
    }
}