
    <?php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateTermTable extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("term", function (Blueprint $table) {

						$table->integer('course_id')->unsigned()->comment('コースID');
						$table->unsignedTinyInteger('order')->comment('ターム順番');
						$table->string('name',32)->comment('ターム名');
						$table->unsignedTinyInteger('term')->comment('期間');
						$table->string('summary',256)->nullable()->comment('ターム概要');
						$table->integer('created_by_id')->comment('作成者ID');
						$table->timestamp('created_at')->comment('作成日');
                        $table->primary(['course_id', 'order']);
						

                    //*********************************
                    // Foreign KEY [ Uncomment if you want to use!! ]
                    //*********************************
                        //$table->foreign("course_id")->references("id")->on("course");



						// ----------------------------------------------------
						// -- SELECT [term]--
						// ----------------------------------------------------
						// $query = DB::table("term")
						// ->leftJoin("course","course.id", "=", "term.course_id")
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
                Schema::dropIfExists("term");
            }
        }
    