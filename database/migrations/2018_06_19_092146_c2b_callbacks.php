<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class C2bCallbacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c2b_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('TransactionType');
            $table->string('TransID');
            $table->string('TransactionCompletedTime');
            $table->unsignedInteger('Amount');
            $table->string('BillRefNumber');
            $table->string('PhoneNumber');
            $table->string('FirstName');
            $table->string('MiddleName');
            $table->string('LastName');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c2b_callbacks');
    }
}
