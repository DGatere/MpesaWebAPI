<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class B2bPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('command_id');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('shortcode');
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
        Schema::dropIfExists('b2b_payments');
    }
}
