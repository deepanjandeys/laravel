<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function orders()
    {
        return $this->hasMany(Order::class);
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
public function getVillages()
    {
       return $this->hasMany(Village::class,'id','village_id');
    }
}
