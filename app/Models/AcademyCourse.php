<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyCourse extends Model
{
    protected $guarded = [];

    public function lessons()
    {
        return $this->hasMany(AcademyLesson::class, 'course_id')->orderBy('order');
    }

    public function mentor()
    {
        return $this->belongsTo(AcademyMentor::class, 'mentor_id');
    }
}
