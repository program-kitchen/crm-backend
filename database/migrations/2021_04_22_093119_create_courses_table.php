
    <?php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateCoursesTable extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("courses", function (Blueprint $table) {

                        $table->increments('id')->comment('コースID');
                        $table->string('name',32)->comment('コース名');
                        $table->unsignedTinyInteger('term')->comment('期間');
                        $table->string('summary',256)->nullable()->comment('コース概要');
                        $table->integer('created_by')->comment('作成者ID');
                        $table->timestamp('created_at')->
                            default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日');
                        $table->integer('updated_by')->comment('更新者ID');
                        $table->timestamp('updated_at')->
                            default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日');
                        $table->softDeletes()->comment('削除日');



                        // ----------------------------------------------------
                        // -- SELECT [courses]--
                        // ----------------------------------------------------
                        // $query = DB::table("courses")
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
                Schema::dropIfExists("courses");
            }
        }
    