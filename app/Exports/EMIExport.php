<?php

namespace App\Exports;

use App\Models\EmiCollections;
use App\Models\Admin; 
use App\Models\Village; 
use App\Models\SalesAgent;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EMIExport implements FromView //FromCollection
{
    public function __construct($request)
    {
        $this->request=$request;
    }
    public function view(): View
    {

/*
        if($this->request->get('bill_id')=='')
            $emis=EmiCollections::all();
        else
            $emis=EmiCollections::where('bill_id','=',$this->request->get('bill_id'))->get();
*/
//$emi_collections=EmiCollections::all();
        $bill_id=$this->request['bill_id'] ?? "";
        $Due_Date_From=$this->request['Due_Date_From'] ?? "";
        $Due_Date_To=$this->request['Due_Date_To'] ?? "";
        $Collect_Date_From=$this->request['Collect_Date_From'] ?? "";
        $Collect_Date_To=$this->request['Collect_Date_To'] ?? "";
        $Receive_Date_From=$this->request['Receive_Date_From'] ?? "";
        $Receive_Date_To=$this->request['Receive_Date_To'] ?? "";
        $cust_name=$this->request['cust_name'] ?? "";
        $cust_mobile=$this->request['cust_mobile'] ?? "";
        $village_ids=$this->request['village_ids'] ?? array();
        $product_ids=$this->request['product_ids'] ?? array();
        $sale_agent_ids=$this->request['sale_agent_ids'] ?? array();
        $status=$request['status'] ?? "";
        $rdb=$request['rdb'] ?? "0";
    /////////
//DB::enableQueryLog();

if($Due_Date_From !='')
        $Due_Date_from =date('Y-m-d',strtotime($Due_Date_From));
    else 
        $Due_Date_from='';

 if($Due_Date_To !='')
    $Due_Date_to =date('Y-m-d',strtotime($Due_Date_To));
 else 
    $Due_Date_to ='';

//////////////////
if($Collect_Date_From !='')
$Collect_Date_from =date('Y-m-d',strtotime($Collect_Date_From));
    else 
        $Collect_Date_from='';

 if($Collect_Date_To !='')
    $Collect_Date_to =date('Y-m-d',strtotime($Collect_Date_To));
 else 
    $Collect_Date_to ='';

//////////////////
if($Receive_Date_From !='')
$Receive_Date_from =date('Y-m-d',strtotime($Receive_Date_From));
    else 
        $Receive_Date_from='';

 if($Receive_Date_To !='')
    $Receive_Date_to =date('Y-m-d',strtotime($Receive_Date_To));
 else 
    $Receive_Date_to ='';

     try
     {
        if($village_ids[0]=='')
            $village_ids=array();
     }
     catch(\Exception $e)
     {

     }

     try
     {
        if($product_ids[0]=='')
            $product_ids=array();
     }
     catch(\Exception $e)
     {
        
     }


     try
     {
        if($sale_agent_ids[0]=='')
            $sale_agent_ids=array();
     }
     catch(\Exception $e)
     {
        
     }
        
        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
        $Villages = Village::all();     
        $SalesAgents =SalesAgent::all();
        //$village_id1=array();
        if(empty($sale_agent_ids))
            {
                if(count($SalesAgents)>0)
                {
                    foreach($SalesAgents as $v)
                    {
                    $sale_agent_id1[]=$v->id;
                    }    
                }
                else 
                {
                    $sale_agent_id1[]=0;
                }
            }

        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $Villages=Village::all();          
            $SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            $sale_agent_id1[]=$sale_agent_id;
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

        $Villages = DB::table('villages')->where('id',$villageAgent->village_id)->get();     
            //echo "<br>(village id ".$village_id.'<br>';
        $sale_agent_id=0;

            if(empty($village_ids))
            {
              //  echo '**'.count($Villages);
                if(count($Villages)>0)
                {
                    foreach($Villages as $v)
                    {
                    $village_id1[]=$v->id;
//                  $sale_agent_id=$v->sale_agent;
                    }    
                }
                else 
                {
                    $village_id1[]=0;
                }
            }
            $SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            $sale_agent_id1[]=$sale_agent_id;
        }
    
    if($bill_id !='' || $cust_name !="" || $cust_mobile !='' || isset($village_ids[0]) || isset($sale_agent_ids[0]) || isset($product_ids[0]) || $Due_Date_From !="" || $Due_Date_To !="" || $Collect_Date_From !="" || $Collect_Date_To !="" || $Receive_Date_From !="" || $Receive_Date_To !="" || $status !='' || $rdb !='0')
        {
        $data = $this->request->all();
        $Products=Product::all();

        if(empty($product_ids))
            {
                //echo '**'.count($Products);
                if(count($Products)>0)
                {
                    foreach($Products as $v)
                    {
                    $product_id1[]=$v->id;
                    }    
                }
                else 
                {
                    //$product_id1[]=0;
                }
            }

            if(!empty($product_id1))
            $data['product_ids']=$product_id1;

        //echo '<br>product id 2';
        //print_r($product_ids);
//////////////
$customer_ids=array();
$customers =  Customer::select('id')
->when(!empty($data['cust_name']) , function ($query) use($data){
return $query->where('name', 'LIKE', '%'.$data['cust_name'].'%');
})
->when (!empty($data['cust_mobile']) , function ($query) use($data){
return $query->where('mobile',$data['cust_mobile']);
})
->when (!empty($village_ids) , function ($query) use($village_ids){
return $query->whereIn('village_id', $village_ids);
})->get();

if(count($customers)>0)
   {
   foreach($customers as $c)
   {
    $customer_ids[]=$c->id;
   }    
  }
 else 
 {
  $customer_ids[]=0;
 }
////////////        
 
$bills =  Bill::select( 'id')
->when(!empty($data['bill_id'])  , function ($query) use($data){
return $query->where('id', '=', $data['bill_id']);
})
->when (!empty($customer_ids) , function ($query) use($customer_ids)
{
return $query->whereIn('customer_id', $customer_ids);
})
->when (!empty($product_ids) , function ($query) use($product_ids)
{
return $query->whereIn('product_id', $product_ids);
})
->when (!empty($sale_agent_ids) , function ($query) use($sale_agent_ids)
{
return $query->whereIn('sale_agent_id', $sale_agent_ids);
})
->when ($status !='' , function ($query) use($data){
return $query->where('status', '=', $data['status']);
})->get();

if(count($bills)>0)
   {
   foreach($bills as $b)
   {
    $bill_ids[]=$b->id;
   }    
  }
 else 
 {
  $bill_ids[]=0;
 }

$emi_collections =  EmiCollections::select( 'id', 'bill_id', 'emi_date','EMI_Loan', 'EMI_interest','emi_amount', 'fine_amount','paid_amt','due_amt', 'collect_time','receive_time')
->when($Due_Date_from !='' && $Due_Date_to !='' , function ($query) use($Due_Date_from,$Due_Date_to){
return $query->whereBetween('emi_date', [$Due_Date_from, $Due_Date_to]);
})
->when($Collect_Date_from !='' && $Collect_Date_to !='' , function ($query) use($Collect_Date_from,$Collect_Date_to){
return $query->whereBetween('collect_time', [$Collect_Date_from, $Collect_Date_to]);
})
->when($Receive_Date_from !='' && $Receive_Date_to !='' , function ($query) use($Receive_Date_from,$Receive_Date_to){
return $query->whereBetween('receive_time', [$Receive_Date_from, $Receive_Date_to]);
})
->when (!empty($bill_ids) , function ($query) use($bill_ids)
{
return $query->whereIn('bill_id', $bill_ids);
});

if(session()->get('ADMIN_TYPE')=='3')
{
$emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
}
else if(session()->get('ADMIN_TYPE')=='4')
{
//$emi_collections=$emi_collections->whereNull('collect_time');

    if($rdb=='1')
    $emi_collections=$emi_collections->whereNull('collect_time');
    if($rdb=='2')
    $emi_collections=$emi_collections->whereNotNull('collect_time');
    if($rdb=='3')
    $emi_collections=$emi_collections->whereNotNull('receive_time');

}

$emi_collections=$emi_collections->get();     
//echo 'Searched';
}
else
{   
//    $btnClass='btn-primary';
    $Products=Product::all();
    $customers = Customer::all();

    $customer_ids=array();
    foreach($customers as $c)
    {
        $customer_ids[]=$c->id;
    }


    
    $Collect_Date_From =date('d-m-Y');
    $Collect_Date_To =date('d-m-Y');
    
    $Collect_Date_from =date('Y-m-d');
    $Collect_Date_to =date('Y-m-d');
     

$bills =  Bill::select( 'id')
->when(!empty($customer_ids) , function ($query) use($customer_ids)
{
return $query->whereIn('customer_id', $customer_ids);
})
->when (!empty($sale_agent_ids) , function ($query) use($sale_agent_ids)
{
return $query->whereIn('sale_agent_id', $sale_agent_ids);
})->get();


if(count($bills)>0)
   {
   foreach($bills as $b)
   {
    $bill_ids[]=$b->id;
   }    
  }
 else 
 {
  //$bill_ids[];
 }

$emi_collections =  EmiCollections::select( 'id', 'bill_id', 'emi_date','EMI_Loan', 'EMI_interest', 'emi_amount', 'paid_amt','due_amt', 'fine_amount', 'collect_time','receive_time')
->when($Due_Date_from !='' && $Due_Date_to !='' , function ($query) use($Due_Date_from,$Due_Date_to){
return $query->whereBetween('emi_date', [$Due_Date_from, $Due_Date_to]);
})
->when($Collect_Date_from !='' && $Collect_Date_to !='' , function ($query) use($Collect_Date_from,$Collect_Date_to){
return $query->whereBetween('collect_time', [$Collect_Date_from, $Collect_Date_to]);
})
->when($Receive_Date_from !='' && $Receive_Date_to !='' , function ($query) use($Receive_Date_from,$Receive_Date_to){
return $query->whereBetween('receive_time', [$Receive_Date_from, $Receive_Date_to]);
})
->when (!empty($bill_ids) , function ($query) use($bill_ids)
{
return $query->whereIn('bill_id', $bill_ids);
});


if(session()->get('ADMIN_TYPE')=='3')
{
$emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
}
else if(session()->get('ADMIN_TYPE')=='4')
{
    if($rdb=='1')
    $emi_collections=$emi_collections->whereNull('collect_time');
    if($rdb=='2')
    $emi_collections=$emi_collections->whereNotNull('collect_time');
    if($rdb=='3')
    $emi_collections=$emi_collections->whereNotNull('receive_time');
}

$emi_collections=$emi_collections->orderBy('emi_date')->get();     
//echo 'All';
}
//print_r(DB::getQueryLog());  
//////////////
        return view('admin.emi-export', [
            'emis' => $emi_collections
        ]);
    }
}
