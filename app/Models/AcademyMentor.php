<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyMentor extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function courses()
    {
        return $this->hasMany(AcademyCourse::class, 'mentor_id');
    }
}
