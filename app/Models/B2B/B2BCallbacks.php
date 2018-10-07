<?php

namespace App\Models\B2B;

use Illuminate\Database\Eloquent\Model;

class B2BCallbacks extends Model
{
    protected $table = 'b2b_callbacks';

    protected $fillable = [
        'ResultCode',
        'OriginatorConversationID',
        'ConversationID',
        'TransactionID',
        'Amount',
        'TransactionCompletedTime',
        'ReceiverPartyPublicName',
        'BillReferenceNumber'
    ];
}
