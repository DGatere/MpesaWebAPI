<?php

namespace App\Models\C2B;

use Illuminate\Database\Eloquent\Model;

class C2BPayment extends Model
{
    protected $table = 'c2b_payments';

    protected $fillable = [
        'Amount',
        'PhoneNumber',
        'BillRefNumber',
    ];
}
