<?php

namespace App\Models\B2C;

use Illuminate\Database\Eloquent\Model;

class B2C_Callbacks extends Model
{
    protected $table = 'b2c_callbacks';

    protected $fillable = [
        'ResultCode',
        'OriginatorConversationID',
        'ConversationID',
        'TransactionID',
        'TransactionAmount'
    ];
}
