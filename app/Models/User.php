<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Contracts\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable , HasRoles,SoftDeletes;

   
    protected $fillable = ['name', 'email', 'number', 'password', 'status', 'reporting_user'];
 
    protected $hidden = [
        'password',
        'remember_token',
    ];

 
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    public function reportingUser()
    {
        return $this->belongsTo(User::class, 'reporting_user');
    }
    public function usersUnder()
    {
        return $this->hasMany(User::class, 'reporting_user');
    }
   


}
