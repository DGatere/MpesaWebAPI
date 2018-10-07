<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLipaNaMpesaPaybillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lipa_na_mpesa_paybills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amount');
            $table->string('number');
            $table->string('account_reference');
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
        Schema::dropIfExists('lipa_na_mpesa_paybills');
    }
}
