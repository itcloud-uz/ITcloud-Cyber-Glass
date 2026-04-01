<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyLesson extends Model
{
    protected $guarded = [];

    protected $casts = [
        'content' => 'array'
    ];

    public function course()
    {
        return $this->belongsTo(AcademyCourse::class);
    }
}
