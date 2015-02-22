<?php

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        DB::statement("SET foreign_key_checks = 0");

        DB::table('reservations')->truncate();

        $this->call('UsersSeeder');
        $this->call('BooksSeeder');

        DB::statement("SET foreign_key_checks = 1");
    }

}
