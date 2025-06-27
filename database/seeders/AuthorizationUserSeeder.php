<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorizationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorization_user[0] = [
            'authorization_id' => 1,
            'user_id' => 1
        ];

        $authorization_user[1] = [
            'authorization_id' => 2,
            'user_id' => 1
        ];

        $authorization_user[2] = [
            'authorization_id' => 3,
            'user_id' => 1
        ];

        $authorization_user[3] = [
            'authorization_id' => 4,
            'user_id' => 1
        ];

        $authorization_user[4] = [
            'authorization_id' => 5,
            'user_id' => 1
        ];

        $authorization_user[5] = [
            'authorization_id' => 6,
            'user_id' => 1
        ];

        $authorization_user[6] = [
            'authorization_id' => 7,
            'user_id' => 1
        ];

        $authorization_user[7] = [
            'authorization_id' => 8,
            'user_id' => 1
        ];

        $authorization_user[8] = [
            'authorization_id' => 9,
            'user_id' => 1
        ];

        DB::table('authorization_user')->insert($authorization_user);
    }
}
