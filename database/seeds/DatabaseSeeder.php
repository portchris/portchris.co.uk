<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PortchrisDatabaseSeeder::class);
        // $this->call(BooksTableSeeder::class);
        $this->command->info('App seeds finished. Portchris-errific!!');
    }
}
