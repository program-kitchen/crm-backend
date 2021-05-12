<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("users", function (Blueprint $table) {
            $table->increments('id')->comment('ユーザID');
            $table->uuid('uuid')->default(DB::raw('(UUID())'))->comment('ユーザUUID');
            $table->string('name',32)->comment('ユーザ名');
            $table->string('password',256)->default('')->comment('ログインパスワード');
            $table->rememberToken()->comment('パスワードリセット用トークン');
            $table->tinyInteger('role')->default(1)->
                comment('ユーザ権限:1:コーチ、2:バックオフィス、3:管理者、4:オーナー');
            $table->string('email',256)->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable()->comment('メール認証日時');
            $table->tinyInteger('is_active')->default(0)->comment('アクティブ:0：無効、1：有効');
            $table->integer('created_by')->comment('作成者ID');
            $table->timestamp('created_at')->
                default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日');
            $table->integer('updated_by')->comment('更新者ID');
            $table->timestamp('updated_at')->
                default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日');
            $table->softDeletes()->comment('削除日');
            $table->unique('uuid');
            $table->unique('email');

            // ----------------------------------------------------
            // -- SELECT [users]--
            // ----------------------------------------------------
            // $query = DB::table("users")
            // ->get();
            // dd($query)For checking
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("users");
    }
}
