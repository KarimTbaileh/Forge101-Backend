<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExerciseProgress extends Model
{
    use HasFactory;

    protected $fillable = ['user_plan_id', 'exercise_id', 'is_completed', 'completed_at'];

    public function userPlan()
    {
        return $this->belongsTo(UserPlan::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
