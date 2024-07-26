<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    use HasFactory;

    public function getBranch()
    {
       return $this->hasMany(Branch::class,'id','branch_id');
    }
    public function getProduct()
    {
       return $this->hasMany(Product::class,'id','product_id');
    }
    public function getUpdatedAtAttribute($value)
    {
        return date('d-m-Y h:i:s A',strtotime($value));   
    }
}
