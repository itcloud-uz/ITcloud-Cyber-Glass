<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $guarded = [];

    protected $casts = [
        'includes' => 'array',
        'extra_services' => 'array'
    ];
}
