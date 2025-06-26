<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsConfig extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'user_id',
        'url',
        'user_id',
        'api_key',
        'sender_id',
        'status'
    ];
}
