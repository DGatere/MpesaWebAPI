<?php

namespace App\Models\LipaNaMpesa;

use Illuminate\Database\Eloquent\Model;

class LipaNaMpesaPaybillPayment extends Model
{
    protected $table = 'lipa_na_mpesa_paybills';

    protected $fillable = [
        'amount',
        'number',
        'account_reference'
    ];
}
