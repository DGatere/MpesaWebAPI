<?php

namespace App\Models\B2C;

use Illuminate\Database\Eloquent\Model;

class B2CPayment extends Model
{
    protected $table = 'b2c_payments';

    protected $fillable = [
        'command_id',
        'amount',
        'number'
    ];
}
