<?php

use Illuminate\Database\Seeder;

class Types_Of_Pickup_TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types_of_pickup')->insert([
            ['id' => 1, 'name' => 'Pick up delivery box'],
            ['id' => 2, 'name' => 'Pick up box on warehouse'],
        ]);
    }
}
