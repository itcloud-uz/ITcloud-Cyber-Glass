<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = ['bot_id', 'file_name', 'content'];

    public function bot()
    {
        return $this->belongsTo(TelegramBot::class, 'bot_id');
    }
}
