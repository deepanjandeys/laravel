<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function VillageAgents()
    {
       return $this->hasOne(VillageAgent::class,'village_id','id');
    }

}
