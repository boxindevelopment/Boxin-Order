<?php

use Illuminate\Database\Seeder;

class Types_Of_Size_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types_of_size')->insert([
            [
                'types_of_box_room_id' => 1,
                'name' => 'Small Box',
                'size' => '60 x 100 cm'
            ],
            [
                'types_of_box_room_id' => 1,
                'name' => 'Medium Box',
                'size' => '100 x 120 cm'
            ],
            [
                'types_of_box_room_id' => 1,
                'name' => 'Large Box',
                'size' => '200 x 100 cm'
            ],
            [
                'types_of_box_room_id' => 2,
                'name' => 'Small Room',
                'size' => '1 x 1 m'
            ],
            [
                'types_of_box_room_id' => 2,
                'name' => 'Medium Room',
                'size' => '3 x 4 m'
            ],
            [
                'types_of_box_room_id' => 2,
                'name' => 'Large Room',
                'size' => '5 x 4 m'
            ],

        ]);
    }
}
