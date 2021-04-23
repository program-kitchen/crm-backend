
    <?php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateCourseTable extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("course", function (Blueprint $table) {

						$table->increments('id')->comment('コースID');
						$table->string('name',32)->comment('コース名');
						$table->unsignedTinyInteger('term')->comment('期間');
						$table->string('summary',256)->nullable()->comment('コース概要');
						$table->integer('created_by_id')->comment('作成者ID');
						$table->timestamp('created_at')->comment('作成日');
						$table->integer('updated_by_id')->comment('更新者ID');
                        $table->timestamp('updated_at')->comment('更新日');
						$table->softDeletes()->comment('削除日');



						// ----------------------------------------------------
						// -- SELECT [course]--
						// ----------------------------------------------------
						// $query = DB::table("course")
						// ->get();
						// dd($query); //For checking



                });
            }

            /**
             * Reverse the migrations.
             *
             * @return void
             */
            public function down()
            {
                Schema::dropIfExists("course");
            }
        }
    