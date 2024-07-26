<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesAgent extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function Admins()
    {
       return $this->hasOne(Admin::class,'refer_id','id');
    }
}
