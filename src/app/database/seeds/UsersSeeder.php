<?php

use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();

        $users = [
            [
                'api_token' => 'token1',
                'name' => '大阪 太郎',
            ],
            [
                'api_token' => 'token2',
                'name' => '神戸 花子',
            ],
            [
                'api_token' => 'token3',
                'name' => '東京 次郎',
            ],
        ];

        $now = Carbon::now();
        foreach ($users as $v) {
            $v['created_at'] = $now;
            $v['updated_at'] = $now;

            DB::table('users')->insert($v);
        }
    }
}

