<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickTransaction extends Model
{
    protected $guarded = [];

    public function academyPayment()
    {
        return $this->belongsTo(AcademyPayment::class, 'merchant_trans_id');
    }
}
