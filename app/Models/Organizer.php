<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizer extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['user_id','name', 'email', 'number', 'address', 'gst_no', 'gst_certificate','company_name','company_letter','event_name'];

    public function compId()
    {
        return $this->belongsTo(Company::class, 'user_id', 'org_id');
    }
}
