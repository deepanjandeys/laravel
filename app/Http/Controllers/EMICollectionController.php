<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Bill;
use App\Models\Admin;
use App\Models\VillageAgent;
use App\Models\Village;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesAgent;
use App\Models\BranchStock;
use App\Models\BranchStockDetails;
use App\Models\EmiCollections;
use App\Exports\EMIExport;
use Maatwebsite\Excel\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \PDF;

class EMICollectionController extends Controller
{
  public function index(Request $request)
    {
        $bill_id=$request['bill_id'] ?? "";
        $Due_Date_From=$request['Due_Date_From'] ?? "";
        $Due_Date_To=$request['Due_Date_To'] ?? "";
        $Collect_Date_From=$request['Collect_Date_From'] ?? "";
        $Collect_Date_To=$request['Collect_Date_To'] ?? "";
        $Receive_Date_From=$request['Receive_Date_From'] ?? "";
        $Receive_Date_To=$request['Receive_Date_To'] ?? "";
        $cust_name=$request['cust_name'] ?? "";
        $cust_mobile=$request['cust_mobile'] ?? "";
        $village_ids=$request['village_ids'] ?? array();
        $product_ids=$request['product_ids'] ?? array();
        $sale_agent_ids=$request['sale_agent_ids'] ?? array();
        $status=$request['status'] ?? "";
        $rdb=$request['rdb'] ?? "0";
        $rows=$request['rows'] ?? "15";
        $village_agent_id=0;
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
                    //$sale_agent_id1[]=0;
                }
            }

        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $Villages=Village::all();   
            $SalesAgents =SalesAgent::all(); 
            $village_ids=array();
            //$SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            //$sale_agent_id1[]=$sale_agent_id;
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
                   // $village_id1[]=0;
                }
            }
            $SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            /*
            $sale_agent_id1[]=$sale_agent_id;
            */
        }
    
    if($bill_id !='' || $cust_name !="" || $cust_mobile !='' || isset($village_ids[0]) || isset($sale_agent_ids[0]) || isset($product_ids[0]) || $Due_Date_From !="" || $Due_Date_To !="" || $Collect_Date_From !="" || $Collect_Date_To !="" || $Receive_Date_From !="" || $Receive_Date_To !="" || $status !='' || $rdb !='0')
        {
        $data = $request->all();
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
//$emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
}
else if(session()->get('ADMIN_TYPE')=='4')
{
//$emi_collections=$emi_collections->whereNull('collect_time');

    
}
if($rdb=='1')
    {
    $emi_collections=$emi_collections->whereNull('collect_time')->whereNull('receive_time');
    //echo '1';
    }
if($rdb=='2')
    {
    $emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
    //echo '2';
    }
if($rdb=='3')
    {
    $emi_collections=$emi_collections->whereNotNull('collect_time')->whereNotNull('receive_time');
    //echo '3';
    }
//die();

$emi_collections=$emi_collections->latest()->simplepaginate($rows);     

        $displaySearch='';
        $a_search_text='Hide Search';
        $btnClass='btn-warning';
}
else
{
   
    $btnClass='btn-primary';
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
     
//////////////////
    if($village_agent_id !=0)
    {
    $villageAgent=VillageAgent::find($village_agent_id);

        $Villages = DB::table('villages')->where('id',$villageAgent->village_id)->get();     
            //echo "<br>(village id ".$village_id.'<br>';

            if(empty($village_ids))
            {
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
                    //$village_id1[]=0;
                }
            }
    }
/////////////////////////////
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
     
}
$emi_collections->where('bill_id','=',0);   
$emi_collections=$emi_collections->orderBy('emi_date')->simplepaginate($rows);     

        $displaySearch='display:none;';
        $a_search_text='Show Search';
        
        }
        
        
        $statuses = DB::table('status')->get(); 
        $getURL = $request->fullUrl();

    $calcmode = DB::table('calcmode')->get(); 
    $result = compact('bill_id','emi_collections','Villages','village_ids','statuses','status','SalesAgents','sale_agent_ids','customers','Products','product_ids','calcmode','displaySearch','a_search_text','cust_name','cust_mobile','Due_Date_From','Due_Date_To','Collect_Date_From','Collect_Date_To','Receive_Date_From','Receive_Date_To','btnClass','rdb','rows','getURL');
//print_r(DB::getQueryLog());  
        return view('admin.emi_list',$result); 
    }
    
public function receive(Request $request)
    {
        $bill_id=$request['bill_id'] ?? "";
        $Date_From=$request['Date_From'] ?? "";
        $Date_To=$request['Date_To'] ?? "";
        $cust_name=$request['cust_name'] ?? "";
        $cust_mobile=$request['cust_mobile'] ?? "";
        $village_ids=$request['village_ids'] ?? array();
        $product_ids=$request['product_ids'] ?? array();
        $sale_agent_ids=$request['sale_agent_ids'] ?? array();
        $status=$request['status'] ?? "";
        $rows=$request['rows'] ?? "15";
    /////////
    //DB::enableQueryLog();

    if($Date_From !='')
        $Date_from =date('Y-m-d',strtotime($Date_From));
    else 
        $Date_from='';

 if($Date_To !='')
    $Date_to =date('Y-m-d',strtotime($Date_To));
 else 
    $Date_to ='';
     
     try
     {
        if($village_ids[0]=='')
            $village_ids=array();
     }
     catch(\Exception $e) {}

     try
     {
        if($product_ids[0]=='')
            $product_ids=array();
     }
     catch(\Exception $e){}

     try
     {
        if($sale_agent_ids[0]=='')
            $sale_agent_ids=array();
     }
     catch(\Exception $e) {}
        
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
                    //$sale_agent_id1[]=0;
                }
            }

        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $Villages=Village::all();          
            $SalesAgents =SalesAgent::all();
            //$SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            //$sale_agent_id1[]=$sale_agent_id;
        }
    
    if($bill_id !='' || $cust_name !="" || $cust_mobile !='' || $Date_From !='' || $Date_To !='' || isset($village_ids[0]) || isset($sale_agent_ids[0]) || isset($product_ids[0]) || $status !='')
        {
        $data = $request->all();
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

$emi_collections =  EmiCollections::select( 'id', 'bill_id', 'emi_date','EMI_Loan','EMI_interest','paid_amt','due_amt', 'emi_amount', 'fine_amount', 'collect_time')
->when($Date_from !='' && $Date_to !='' , function ($query) use($Date_from,$Date_to){
return $query->whereBetween('collect_time', [$Date_from, $Date_to]);
})
->when (!empty($bill_ids) , function ($query) use($bill_ids)
{
return $query->whereIn('bill_id', $bill_ids);
});

if(session()->get('ADMIN_TYPE')=='3')
{
$emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
}

$emi_collections=$emi_collections->latest()->simplepaginate($rows);     
        $displaySearch='';
        $a_search_text='Hide Search';
        $btnClass='btn-warning';
}
else
{   
    $btnClass='btn-primary';
    $Products=Product::all();
    $customers = Customer::all();
/*
    $customer_ids=array();
    foreach($customers as $c)
    {
        $customer_ids[]=$c->id;
    }

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
*/
  $Date_from =date('Y-m-d');
    $Date_to =date('Y-m-d');
    
    $Date_From =date('d-m-Y');
    $Date_To =date('d-m-Y');
$emi_collections =  EmiCollections::select( 'id', 'bill_id', 'emi_date','EMI_Loan','EMI_interest','paid_amt','due_amt', 'emi_amount', 'fine_amount', 'collect_time')
->when($Date_from !='' && $Date_to !='' , function ($query) use($Date_from,$Date_to){
return $query->whereBetween('collect_time', [$Date_from, $Date_to]);
});


if(session()->get('ADMIN_TYPE')=='3')
{
$emi_collections=$emi_collections->whereNotNull('collect_time')->whereNull('receive_time');
}

$emi_collections=$emi_collections->orderBy('emi_date')->simplepaginate($rows);     

        $displaySearch='display:none;';
        $a_search_text='Show Search';
}
        
        $statuses = DB::table('status')->get(); 


    $calcmode = DB::table('calcmode')->get(); 
    $result = compact('bill_id','emi_collections','Villages','village_ids','statuses','status','SalesAgents','sale_agent_ids','customers','Products','product_ids','calcmode','displaySearch','a_search_text','cust_name','cust_mobile','Date_From','Date_To','btnClass','rows');
//print_r(DB::getQueryLog());  
        return view('admin.emi_recieve_bySA',$result); 
    }
    
    public function emi_collection(Request $request,$id='')
    {

$emi_amount=0;$fine_amount=0; $bill_id=0;
        if ($id>0) 
        {
            $emi = EmiCollections::find($id);
            /*
             if($emi->collect_time=='')
                $emi->collect_time=date('d-m-Y');
                */
            $result['emi']=$emi;
/////////////////////

$date1=date_create($emi->emi_date);
$date2=date_create(date('Y-m-d'));
$diff=date_diff($date1,$date2);
$noOfDays=(int)($diff->format("%r%a"));
if($noOfDays>0)
    $fine_amount=$noOfDays*50;
else  
    $fine_amount=0;

$emi->fine_amount=number_format($fine_amount,2);
$emi_amount=$emi->emi_amount;
$bill_id=$emi->bill_id;
$result['noOfDays']=$noOfDays;
//$result['fine']=$fine;///////////
            $query = DB::select( DB::raw("SELECT count(*) as c FROM `emi_collections` WHERE bill_id=".$emi->bill_id." and `collect_time` is not null and `deleted_at` is Null;"));
            if($query !=null)
            {
                $result['noOf_unPaidEMI']=$query[0]->c;
            }
            else 
            {
                $result['noOf_unPaidEMI']=0;
            }

            $sql= "SELECT `due_amt` from  `emi_collections` where `id`=(SELECT max(`id`) as `id` FROM `emi_collections` WHERE bill_id=".$emi->bill_id." and collect_time is not null);";
            //echo "<br>$sql";
            $query = DB::select( DB::raw($sql));
            if($query !=null)
            {
                $result['due_amt']=$query[0]->due_amt;
            }
            else 
            {
                $result['due_amt']=0;
            }

        }
        else
        {
        //////////////////////
            $result['emi']=0;
            $result['noOfDays']=0;
            $result['noOf_unPaidEMI']=0;
            $result['due_amt']=0;
        }

$paybleAmt=number_format($emi_amount+$fine_amount+$result['due_amt'],2);
$result['paybleAmt']=$paybleAmt;
$result['paidAmt']=$paybleAmt;


$previousEMIs=EmiCollections::where('bill_id',$bill_id)->whereNotNull('collect_time')->get();

$result['previousEMIs']=$previousEMIs;

    //$ADMIN_TYPE=session()->get('ADMIN_TYPE');
    /*
    if($ADMIN_TYPE=='3') 
        return view('admin.edit_emiCollectionBySA',$result); 
    else if($ADMIN_TYPE=='4' || $ADMIN_TYPE=='1')   */
       return view('admin.edit_emiCollection',$result); 
    }

public function print_emi(Request $request,$id='')
    {
        if ($id>0) 
        {
            $emi=EmiCollections::find($id);

            $arr = Bill::where(['id'=>$emi->bill_id])->get();
            $result['id']= $arr[0]->id;
            $result['bill_date']= $arr[0]->bill_date;
            $result['customer_id']= $arr[0]->customer_id;
        $result['Customer_name']= $arr[0]->getCustomers[0]->name;
            $result['Address']= $arr[0]->getCustomers[0]->address;
            $result['mobile']= $arr[0]->getCustomers[0]->mobile;
              
            //$result['Sale_agent_id']= $arr[0]->Sale_agent_id;
        $result['sale_agent']= $arr[0]->getSalesAgent[0]->name;   
            $result['product_name']= $arr[0]->getProduct[0]->name;
            $result['product_price']=$arr[0]->sale_price;
            $result['down_payment']=$arr[0]->down_payment;
            $result['EMI']= $arr[0]->EMI;
            //$result['EMI_mode']= $arr[0]->EMI_mode;
            $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
            $result['EMI_Period']= $arr[0]->EMI_Period;
            $result['LoanAmount']=$arr[0]->LoanAmount;
            $result['IntOnLoan']=$arr[0]->IntOnLoan;
            $result['status']= $arr[0]->status;
            $result['booking_advance']=$arr[0]->booking_advance;
            $result['balanced_downpayment']=$arr[0]->balanced_downpayment;
            if($arr[0]->interestPercntage>0)
                $result['interestPercntage']= $arr[0]->interestPercntage;
            else 
                $result['interestPercntage']='';
            
            $result['order_id']= $arr[0]->order_id;
            
            $EmiMode=DB::table('calcmode')->find($arr[0]->EMI_mode);
            $result['EMI_mode']=$EmiMode->name;
        $emi_collections=EmiCollections::where('bill_id','=',$emi->bill_id)->orderBy('id')->get(); 
        $result['emi_collections'] = $emi_collections;
        $result['current_emi_no'] = $id;
        }

        //return view('admin.print_bill',$result); 
        $pdf = PDF::loadView('admin.print_emi', $result);
        return $pdf->download('emi_'.$id.'.pdf');
    }

    public function manage_emi_collect(Request $request)
    {
        
       $request->validate([
        'collect_time'=>'required',
            ]
       ); 
//DB::enableQueryLog();      
       $emi_id=0;
       if ($request->post('id')>0) 
       {
           $model = EmiCollections::find($request->post('id'));
           $emi_id=$model->id;
           $msg = 'EMI updated';
           
           $paybleAmt= (float)str_replace(',','',$request->post('paybleAmt'));
           $fine_amount= (float)str_replace(',','',$request->post('fine_amount'));
           $paidAmt= (float)str_replace(',','',$request->post('paidAmt'));
           $due_amt=$paybleAmt-$paidAmt;

           $model->fine_amount=$fine_amount;
           $model->paid_amt=$paidAmt;
           $model->due_amt=$due_amt;
           $model->collect_by=session()->get('ADMIN_ID');

           $model->collect_time = $request->post('collect_time');

           if(session()->get('ADMIN_TYPE')=='3')
           {
            $model->recieve_by=session()->get('ADMIN_ID');
            $model->receive_time = date('Y-m-d H:i:s',strtotime($request->post('collect_time')));
           }

           $model->save();
       }
//print_r(DB::getQueryLog());  
       $typeName=session()->get('typeName');
       return redirect($typeName.'/emis/emi_submitted/'.$emi_id);
    }

public function manage_emi_receive(Request $request)
    {
    $data = $request->all();
//DB::enableQueryLog();      
    foreach ($data as $key => $value) 
    {
  //      echo $key;
        if(substr( $key, 0, 3 )==='chk')
        {
    //        echo '<br>'.$value;
        $model = EmiCollections::find($value);
        $model->recieve_by=session()->get('ADMIN_ID');
        $model->receive_time = date('Y-m-d H:i:s',strtotime($request->post('receive_time')));
        $model->save();            
        }
        
    }
//print_r(DB::getQueryLog());
//die();
       $typeName=session()->get('typeName');
       return redirect($typeName.'/emis/receive/');
    }

public function emi_submitted(Request $request,$emi_id=0)
{
    $result['emi_id']=$emi_id;
    //print_r($result);
    return view('admin.emi_submitted',$result); 
}


public function export(Excel $excel,Request $request)
    {
        return $excel->download(new EMIExport($request),'EMI.xlsx');
    }   
}
