<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'profile_image'
    ];


    public $incrementing = false;


    protected $keyType = 'string';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function createdPlans()
    {
        return $this->hasMany(Plan::class, 'created_by_user_id');
    }

    public function enrolledPlans()
    {
        return $this->hasMany(UserPlan::class);
    }
}
