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
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;


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
 
    public function reportingUserLevel2()
    {
        return $this->reportingUser()->with('reportingUser');
    }
    public function usersUnder()
    {
        return $this->hasMany(User::class, 'reporting_user');
    }
    public function organisation()
    {
        return $this->belongsTo(Organizer::class, 'id', 'user_id');
    }
    public function userOrganisation()
    {
        return $this->belongsTo(Organizer::class, 'org_id', 'user_id');
    }
    public function userCompany()
    {
        return $this->belongsTo(Company::class, 'comp_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'id', 'user_id');
    }

    public function companyNew()
    {
        return $this->hasOne(Company::class, 'user_id', 'id');
    }

    public function organizerNew()
    {
        return $this->hasOne(User::class, 'id', 'reporting_user')
                    ->with('reportingUser');
    }

    public function userCompanyName()
    {
        return $this->belongsTo(Company::class, 'reporting_user', 'user_id');
    }

    public function userOrgName()
    {
        return $this->belongsTo(Organizer::class, 'reporting_user', 'user_id');
    }
    public function zoneData()
    {
        return $this->hasMany(Zone::class);
    }
}
