<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyPayment extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(AcademyCourse::class, 'course_id');
    }
}
