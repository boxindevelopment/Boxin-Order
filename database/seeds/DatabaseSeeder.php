<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    	$this->call(Status_TableSeeder::class);
        $this->call(Types_Of_Box_Room_TableSeeder::class);
        $this->call(Types_Of_Duration_TableSeeder::class);
        $this->call(Types_Of_Pickup_TableSeeder::class);
        $this->call(Types_Of_Size_TableSeeder::class);
        $this->call(Prices_TableSeeder::class);
    }
}
