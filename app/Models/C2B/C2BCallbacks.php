<?php

namespace App\Models\C2B;

use Illuminate\Database\Eloquent\Model;

class C2BCallbacks extends Model
{
    protected $table = 'c2b_callbacks';

    protected $fillable = [
        'TransactionType',
        'TransID',
        'TransactionCompletedTime',
        'Amount',
        'BillRefNumber',
        'PhoneNumber',
        'FirstName',
        'MiddleName',
        'LastName',
    ];
}
