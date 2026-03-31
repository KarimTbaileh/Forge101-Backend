<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanExercise extends Pivot
{
    protected $table = 'plan_exercises';
    public $timestamps = true;

    protected $fillable = ['sets', 'reps', 'day', 'order_index'];
}
