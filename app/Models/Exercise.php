<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'target_muscle', 'description', 'image_url',
        'video_url', 'difficulty', 'category'
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_exercises')
            ->using(PlanExercise::class)
            ->withPivot('sets', 'reps', 'day', 'order_index')
            ->withTimestamps();
    }
}
