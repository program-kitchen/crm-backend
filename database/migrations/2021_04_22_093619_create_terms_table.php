
    <?php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateTermsTable extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("terms", function (Blueprint $table) {

                        $table->integer('course_id')->unsigned()->comment('コースID');
                        $table->unsignedTinyInteger('order')->comment('ターム順番');
                        $table->string('name',32)->comment('ターム名');
                        $table->unsignedTinyInteger('term')->comment('期間');
                        $table->string('summary',256)->nullable()->comment('ターム概要');
                        $table->integer('created_by')->comment('作成者ID');
                        $table->timestamp('created_at')->
                            default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日');
                        $table->primary(['course_id', 'order']);

                        //*********************************
                        // Foreign KEY [ Uncomment if you want to use!! ]
                        //*********************************
                        //$table->foreign("course_id")->references("id")->on("course");



                        // ----------------------------------------------------
                        // -- SELECT [terms]--
                        // ----------------------------------------------------
                        // $query = DB::table("terms")
                        // ->leftJoin("course","course.id", "=", "terms.course_id")
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
                Schema::dropIfExists("terms");
            }
        }
    