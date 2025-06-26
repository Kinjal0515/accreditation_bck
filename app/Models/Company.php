<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['user_id','name', 'email', 'number', 'address', 'gst_no', 'gst_certificate','category_id','company_name','company_letter','org_id','zone'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
 
    public function categoryId()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}