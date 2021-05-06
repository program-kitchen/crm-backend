<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ユーザテーブルの初期化
        \DB::table('users')->delete();

        // 初期ユーザ登録
        User::create([
            'name' => 'オーナー',
            'password' => \Crypt::encrypt('owner-2021'),
            'role' => 4,
            'email' => 'test@test.com',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
