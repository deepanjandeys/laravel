<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product_sub_group extends Model
{
    protected $table="product_sub_groups";
    protected $primaryKey ="id";
    use HasFactory;
    use SoftDeletes;
}
