<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProject extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config' => 'array'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
