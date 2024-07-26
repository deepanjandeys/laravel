<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VillageAgent extends Model
{
    use HasFactory;
    use SoftDeletes;
    /*
    protected $table="villageagent";
    protected $primaryKey ="id";
    */
/*
     public function Villages() {
      return $this->belongsTo(Village::class); 
    }
*/
    public function Admins()
    {
       return $this->hasOne(Admin::class,'refer_id','id');
    }

    public function IDProofImagePath()
      {
      if (file_exists( public_path() . '/storage/media/' . $this->IdProofImage)) {
      return '/storage/media/' . $this->IdProofImage;
      } 
      else 
      {
      return '/storage/media/NoImage.png';
      }
}
public function AddressProofImagePath()
      {
      if (file_exists( public_path() . '/storage/media/' . $this->AddressProofImage)) {
      return '/storage/media/' . $this->AddressProofImage;
      } 
      else 
      {
      return '/storage/media/NoImage.png';
      }
}

}
