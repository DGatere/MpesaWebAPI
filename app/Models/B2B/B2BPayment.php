<?php

namespace App\Models\B2B;

use Illuminate\Database\Eloquent\Model;

class B2BPayment extends Model
{
    protected $table = 'b2b_payments';

    protected $fillable = [
        'command_id',
        'amount',
        'shortcode',
        'account_reference'
    ];
}
