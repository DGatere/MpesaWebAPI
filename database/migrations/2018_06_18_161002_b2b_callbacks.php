<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class B2bCallbacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ResultCode');
            $table->string('OriginatorConversationID');
            $table->string('ConversationID');
            $table->string('TransactionID');
            $table->unsignedInteger('Amount');
            $table->string('TransactionCompletedTime');
            $table->string('ReceiverPartyPublicName');
            $table->string('BillReferenceNumber');
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
        Schema::dropIfExists('b2b_callbacks');
    }
}
