<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function ImagePath()
      { // used in edit Product
        if(is_null($this->image))
        {
          return '/storage/media/NoImage.png';
        }
      else if (file_exists( public_path() . '/storage/media/' . $this->image)) {
         return '/storage/media/' . $this->image;
      } 
      else 
      {
          return '/storage/media/NoImage.png';
      }
    }

    public function getImageAttribute($value)
    { // used in list view
       if(is_null($value))
        {
          return '/NoImage.png';
        }
      else if (file_exists( public_path() . '/storage/media/' . $value)) {
         return $value;
      } 
      else 
      {
          return 'NoImage.png';
      }
       // return date('d-m-Y',strtotime($value));   
    }
    public function getGroups()
    {
       return $this->hasMany(ProductGroup::class,'id','GroupId');
    }
    public function getSubGroups()
    {
       return $this->hasMany(Product_sub_group::class,'id','SubGroupId');
    }
}
