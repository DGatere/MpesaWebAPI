<?php

namespace App\Models\LipaNaMpesa;

use Illuminate\Database\Eloquent\Model;

class LipaNaMpesaPaybillCallbacks extends Model
{
    protected $table = 'lnmp_callbacks';

    protected $fillable = [
        'MerchantRequestID',
        'CheckoutRequestID',
        'ResultCode',
        'Amount',
        'TransactionID',
        'TransactionCompletedTime',
        'PhoneNumber'
    ];
}
