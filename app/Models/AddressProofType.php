<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressProofType extends Model
{
    //public $timestamps = false;
    use HasFactory;
    use SoftDeletes;
}
