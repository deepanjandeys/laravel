<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class EmiCollections extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table="emi_collections";
    protected $primaryKey ="id";

    public function getBills()
    {
       return $this->hasMany(Bill::class,'id','bill_id');
    }

    public function setEmiDateAttribute($value)
    {
        $this->attributes['emi_date'] = date('Y-m-d',strtotime($value));
    }

    public function getEmiDateAttribute($value)
    {
        return date('d-m-Y',strtotime($value));   
    }

    public function setCollectTimeAttribute($value)
    {
        $this->attributes['collect_time'] = date('Y-m-d H:i:s',strtotime($value));
    }

    public function getCollectTimeAttribute($value)
    {
        $retValue=date('d-m-Y H:i:s',strtotime($value));

        if($retValue=='01-01-1970 00:00:00')
            $retValue='';   //date('d-m-Y H:i:s');
        
        return $retValue;   
    }

public function setReceiveTimeAttribute($value)
    {
        $this->attributes['receive_time'] = date('Y-m-d H:i:s',strtotime($value));
    }

public function getReceiveTimeAttribute($value)
    {
        $retValue=date('d-m-Y H:i:s',strtotime($value));

        if($retValue=='01-01-1970 00:00:00')
            $retValue='';   //date('d-m-Y H:i:s');
        
        return $retValue;   
    }
    public function setEMILoanAttribute($value)
    {
        $this->attributes['EMI_Loan'] = (float)str_replace(",","",$value);
    }
    public function getEMILoanAttribute($value)
    {
        return number_format($value,2);   
    }

    public function setEMIInterestAttribute($value)
    {
        $this->attributes['EMI_interest'] = (float)str_replace(",","",$value);
    }
    public function getEMIInterestAttribute($value)
    {
        return number_format($value,2);   
    }
        public function setEmiAmountAttribute($value)
    {
        $this->attributes['emi_amount'] = (float)str_replace(",","",$value);
    }
    public function getEmiAmountAttribute($value)
    {
        return number_format($value,2);   
    }

    public function setFineAmountAttribute($value)
    {
        $this->attributes['fine_amount'] = (float)str_replace(",","",$value);
    }
    public function getFineAmountAttribute($value)
    {
        return number_format($value,2);   
    }

    public function setPaidAmtAttribute($value)
    {
        $this->attributes['paid_amt'] = (float)str_replace(",","",$value);
    }
    public function getPaidAmtAttribute($value)
    {
        return number_format($value,2);   
    }

    public function setDueAmtAttribute($value)
    {
        $this->attributes['due_amt'] = (float)str_replace(",","",$value);
    }
    public function getDueAmtAttribute($value)
    {
        return number_format($value,2);   
    }
}
?>