<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class Bill extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table="bills";
    protected $primaryKey ="id";
    //protected $fillable = ['balanced_downpayment'];
        
    public function getCustomers()
    {
       return $this->hasMany(Customer::class,'id','customer_id');
    }
    public function getSalesAgent()
    {
       return $this->hasMany(SalesAgent::class,'id','Sale_agent_id');
    }
    public function getProduct()
    {
       return $this->hasMany(Product::class,'id','product_id');
    }
    public function Order()
    {
       return $this->hasOne(Order::class,'id','order_id');
    }

public function setBillDateAttribute($value)
    {
        $this->attributes['bill_date'] = date('Y-m-d',strtotime($value));
    }

    public function getBillDateAttribute($value)
    {
        return date('d-m-Y',strtotime($value));   
    }
    public function getSalePriceAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setSalePriceAttribute($value)
    {
        $this->attributes['sale_price'] = (float)str_replace(",","",$value);
    }
 
    public function getDownPaymentAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setDownPaymentAttribute($value)
    {
        $this->attributes['down_payment'] = (float)str_replace(",","",$value);
    }
    public function getEMIAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setEMIAttribute($value)
    {
        $this->attributes['EMI'] = (float)str_replace(",","",$value);
    }

    public function getEMILoanAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setEMILoanAttribute($value)
    {
        $this->attributes['EMI_Loan'] = (float)str_replace(",","",$value);
    }

    public function getEMIInterestAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setEMIInterestAttribute($value)
    {
        $this->attributes['EMI_Interest'] = (float)str_replace(",","",$value);
    }
    public function getLoanAmountAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setLoanAmountAttribute($value)
    {
        $this->attributes['LoanAmount'] = (float)str_replace(",","",$value);
    }

    public function getIntOnLoanAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setIntOnLoanAttribute($value)
    {
        $this->attributes['IntOnLoan'] = (float)str_replace(",","",$value);
    }

    public function getBookingAdvanceAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setBookingAdvanceAttribute($value)
    {
        $this->attributes['booking_advance'] = (float)str_replace(",","",$value);
    }

    public function getBalanceDownpaymentAttribute($value)
    {
        return number_format($value,2);   
    }
    public function setBalanceDownpaymentAttribute($value)
    {
        $this->attributes['balanced_downpayment'] = (float)str_replace(",","",$value);
    }
}
?>