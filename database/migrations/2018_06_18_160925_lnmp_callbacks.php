<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LnmpCallbacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lnmp_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('MerchantRequestID');
            $table->string('CheckoutRequestID');
            $table->integer('ResultCode');
            $table->unsignedInteger('Amount');
            $table->string('TransactionID');
            $table->string('TransactionCompletedTime');
            $table->string('PhoneNumber');
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
        Schema::dropIfExists('lnmp_callbacks');
    }
}
