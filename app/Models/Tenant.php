<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_active_at' => 'datetime',
        'files' => 'array'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function aiLogs()
    {
        return $this->hasMany(AiLog::class);
    }
}
