<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'difficulty', 'duration_minutes', 'created_by_user_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'plan_exercises')
            ->using(PlanExercise::class)
            ->withPivot('sets', 'reps', 'day', 'order_index')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(UserPlan::class);
    }


}
