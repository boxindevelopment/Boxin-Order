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
            ['name' => 'Small'],
            ['name' => 'Medium'],
            ['name' => 'Large'],
        ]);
    }
}
