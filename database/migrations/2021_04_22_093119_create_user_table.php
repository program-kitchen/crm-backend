
    <?php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateUserTable extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("user", function (Blueprint $table) {

						$table->increments('id')->comment('ユーザID');
                        $table->integer('uuid')->comment('ユーザUUID');
						$table->string('name',32)->comment('ユーザ名');
						$table->string('password',32)->nullable()->default('')->comment('ログインパスワード');
						$table->tinyInteger('role')->default(1)->comment('ユーザ権限:1:コーチ、2:バックオフィス、3:管理者、4:オーナー');
						$table->string('email',256)->comment('メールアドレス');
						$table->tinyInteger('is_active')->default(0)->comment('アクティブ:0：無効、1：有効');
						$table->integer('created_by_id')->comment('作成者ID');
						$table->timestamp('created_at')->comment('作成日');
						$table->integer('updated_by_id')->comment('更新者ID');
                        $table->timestamp('updated_at')->comment('更新日');
						$table->softDeletes()->comment('削除日');
						$table->unique('uuid');
						$table->unique('email');



						// ----------------------------------------------------
						// -- SELECT [user]--
						// ----------------------------------------------------
						// $query = DB::table("user")
						// ->get();
						// dd($query)For checking



                });

                // uuidのbinary型はmigrationでは生成できないのでSQLで変更
                DB::statement("ALTER TABLE `user` MODIFY `uuid` binary(16) NOT NULL COMMENT 'ユーザUUID'");
            }

            /**
             * Reverse the migrations.
             *
             * @return void
             */
            public function down()
            {
                Schema::dropIfExists("user");
            }
        }
    