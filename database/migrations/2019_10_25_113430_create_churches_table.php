<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChurchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('churches', function (Blueprint $table) {
            $table->bigIncrements('church_id');
            $table->string('church_name',150);
            $table->string('church_address',200);
            $table->string('church_phone',50);
            $table->string('church_email',100);
            $table->string ('church_pastor',100);
            $table->dateTime('created_at',6);
            $table->dateTime('updated_at',6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('churches');
    }
}
