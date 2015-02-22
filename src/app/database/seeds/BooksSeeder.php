<?php

use Carbon\Carbon;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('books')->truncate();

        $books = [
            [
                'asin' => 'B00F418SQ8',
                'title' => 'Vagrant入門ガイド',
                'price' => 400,
                'inventory' => 10,
            ],
            [
                'asin' => '4774159719',
                'title' => 'PHPエンジニア養成読本',
                'price' => 2138,
                'inventory' => 10,
            ],
            [
                'asin' => '4774153249',
                'title' => 'CakePHP2 実践入門',
                'price' => 3110,
                'inventory' => 2,
            ],
        ];

        foreach ($books as $v) {
            $v['created_at'] = Carbon::now();
            $v['updated_at'] = Carbon::now();

            DB::table('books')->insert($v);
        }
    }
}

