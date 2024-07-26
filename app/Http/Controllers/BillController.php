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
use App\Exports\BillExport;
use Maatwebsite\Excel\Excel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \PDF;
class billController extends Controller
{
  public function index(Request $request)
    {
        $id=$request['id'] ?? "";
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
     
     try
     {
        if($village_ids[0]=='')
            $village_ids=array();
     }
     catch(\Exception $e)
     {

     }
//print_r($village_ids);

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
            //$village_id1=array();
          
            $SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            $sale_agent_id1[]=$sale_agent_id;
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

        $Villages = Village::where('id',$villageAgent->village_id)->get();     
            //echo "<br>(village id ".$village_id.'<br>';
        //$sale_agent_id=0;

            if(empty($village_ids))
            {
              //  echo '**'.count($Villages);
                if(count($Villages)>0)
                {
                    foreach($Villages as $v)
                    {
                    $village_id1[]=$v->id;
           //         $sale_agent_id=$v->sale_agent;
                    }    
                }
                else 
                {
                    //$village_id1[]=0;
                }
            }
            $SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
            $sale_agent_id1[]=$sale_agent_id;
        }
    
    if($id !='' || $cust_name !="" || $cust_mobile !='' || $Date_From !='' || $Date_To !='' || isset($village_ids[0]) || isset($sale_agent_ids[0]) || isset($product_ids[0]) || $status !='')
        {
//DB::enableQueryLog();
        $data = $request->all();
///////////////////////
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

  //      echo '<br>village id 2';
  //      print_r($data['village_ids']);
//////////////
$customer_ids=array();
$customers =  Customer::select('id')
->when(!empty($data['cust_name']) , function ($query) use($data){
return $query->where('name', 'LIKE', '%'.$data['cust_name'].'%');
})
->when (!empty($data['cust_mobile']) , function ($query) use($data){
return $query->where('mobile','LIKE','%'.$data['cust_mobile'].'%');
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
 if($Date_From !='')
    $Date_from =date('Y-m-d',strtotime($Date_From));
 else 
    $Date_from='';

 if($Date_To !='')
    $Date_to =date('Y-m-d',strtotime($Date_To));
 else 
    $Date_to ='';

if($Date_to=='')
    $Date_to = $Date_from;

$bills =  Bill::select( 'id', 'bill_date', 'customer_id', 'Sale_agent_id', 'product_id', 'sale_price', 'down_payment', 'EMI_Loan','EMI_Interest', 'EMI', 'EMI_mode', 'EMI_in_Months', 'EMI_Period', 'LoanAmount', 'interestPercntage', 'IntOnLoan', 'status', 'booking_advance','final_submit')
->when(!empty($data['id'])  , function ($query) use($data){
return $query->where('id', '=', $data['id']);
})
->when($Date_from !='' && $Date_to !='' , function ($query) use($Date_from,$Date_to){
return $query->whereBetween('bill_date', [$Date_from, $Date_to]);
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
})
->latest()->simplepaginate($rows);     
//print_r(DB::getQueryLog());  
        $displaySearch='';
        $a_search_text='Hide Search';
}
else
{   
    $Products=Product::all();
    $customers = Customer::all();

    $customer_ids=array();
    foreach($customers as $c)
    {
        $customer_ids[]=$c->id;
    }


$bills =  Bill::select( 'id', 'bill_date', 'customer_id', 'Sale_agent_id', 'product_id', 'sale_price', 'down_payment', 'EMI_Loan','EMI_Interest', 'EMI', 'EMI_mode', 'EMI_in_Months', 'EMI_Period', 'LoanAmount', 'interestPercntage', 'IntOnLoan', 'status', 'booking_advance','final_submit')
->when(!empty($customer_ids) , function ($query) use($customer_ids)
{
return $query->whereIn('customer_id', $customer_ids);
})
->latest()->simplepaginate($rows);     
        $displaySearch='display:none;';
        $a_search_text='Show Search';
        }
        
        $statuses = DB::table('status')->get(); 
        
    $calcmode = DB::table('calcmode')->get(); 
    $result = compact('id','bills','Villages','village_ids','statuses','status','SalesAgents','sale_agent_ids','customers','Products','product_ids','calcmode','displaySearch','a_search_text','cust_name','cust_mobile','Date_From','Date_To','rows');
        return view('admin.bill',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   Bill::onlyTrashed()->get();
        $result['customers'] = DB::table('customers')->get(); 
        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.bill-trash',$result); 
    }

    public function edit_bill(Request $request,$id='',$order_id=0)
    {
        $errMsg="";
        if ($id>0) 
        {
            $arr = Bill::find($id);
            if($arr==null)
            {
                $arr=Bill::withTrashed()->find($id);
                $errMsg="Bill was deleted, but you still update this bill, changes shown if you restored the bill";
            }
            //print_r($arr);
            $result['id']= $arr->id;
            $result['bill_date']= $arr->bill_date;
            $result['customer_id']= $arr->customer_id;
            $result['Sale_agent_id']= $arr->Sale_agent_id;
            $result['product_id']= $arr->product_id;
            $result['sale_price']= $arr->sale_price;
            $result['down_payment']= $arr->down_payment;
            $result['EMI']= $arr->EMI;
            $result['EMI_mode']= $arr->EMI_mode;
            $result['EMI_Loan']= $arr->EMI_Loan;
            $result['EMI_Interest']= $arr->EMI_interest;
            $result['EMI_in_Months']= $arr->EMI_in_Months;
            $result['EMI_Period']= $arr->EMI_Period;
            $result['LoanAmount']= $arr->LoanAmount;
            $result['IntOnLoan']= $arr->IntOnLoan;
            $result['status']= $arr->status;
            $result['booking_advance']= $arr->booking_advance;
            $result['balanced_downpayment']= $arr->balanced_downpayment;
            $result['interestPercntage']= $arr->interestPercntage;
            $result['order_id']= $arr->order_id;
            $result['remarks']= $arr->remarks;
            $result['ADMIN_TYPE'] = session()->get('ADMIN_TYPE');

        }
        else
        {
            
            if($order_id>0)
            {
                $arr = Order::where(['id'=>$order_id])->get();
                $result['id']= 0;
                $result['bill_date']= date('d-m-Y');
                $result['customer_id']= $arr[0]->customer_id;
                $result['Sale_agent_id']= $arr[0]->Sale_agent_id;
                $result['product_id']= $arr[0]->product_id;
                $result['sale_price']= $arr[0]->sale_price;
                
                $result['EMI_Loan']= $arr[0]->EMI_Loan;
                $result['EMI_Interest']= $arr[0]->EMI_Interest;
                $result['EMI']= $arr[0]->EMI;
                $result['EMI_mode']= $arr[0]->EMI_mode;
                $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
                $result['EMI_Period']= $arr[0]->EMI_Period;
                $result['LoanAmount']= (float)str_replace(",","",$arr[0]->LoanAmount);
                $result['IntOnLoan']= $arr[0]->IntOnLoan;
                $result['status']= $arr[0]->status;
                $result['down_payment']=(float)str_replace(",","",$arr[0]->down_payment)-(float)str_replace(",","",$arr[0]->booking_advance);
                $result['booking_advance']=(float)str_replace(",","",$arr[0]->booking_advance);
                $result['balanced_downpayment']= (float)str_replace(",","",$arr[0]->down_payment);
                $result['interestPercntage']= $arr[0]->interestPercntage;
                $result['order_id']= $arr[0]->id;
                $result['remarks']= '';
                $result['ADMIN_TYPE'] = session()->get('ADMIN_TYPE');
            }
            else 
            {
                $result['id']= '0';
            $result['bill_date']= date('d-m-Y');
            $result['customer_id']= '';
            $result['Sale_agent_id']= ''; //$Sale_agent_id;
            $result['product_id']= '';
            $result['sale_price']= '';
            $result['down_payment']= '';
            $result['EMI_Loan']= 0;
            $result['EMI_Interest']= 0; 
            $result['EMI']= '';
            $result['EMI_mode']= '';
            $result['EMI_in_Months']= 0;
            $result['EMI_Period']= '';
            $result['LoanAmount']= '0';
            $result['IntOnLoan']= '0';
            $result['status']= '1';
            $result['booking_advance']= 0;
            $result['balanced_downpayment']= 0;
            $result['interestPercntage']= 0;
            $result['order_id']= $order_id;
            $result['remarks']= '';
            $result['ADMIN_TYPE'] = session()->get('ADMIN_TYPE');
            }
            
        }
        //echo '<br>Result <br>';
        //print_r($result);

        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
            $customers = Customer::all();

            if($id==0 && $order_id==0)
            $result['Sale_agent_id']= '';
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;

            $customers = Customer::all();

            $result['Sale_agent_id']= $sale_agent_id;
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);
            $village = Village::find($villageAgent->village_id);
            $sale_agent_id=$village->sale_agent;

      $customers = DB::table('customers')
        ->join('villages', 'villages.id', '=', 'customers.village_id')
    ->select(DB::raw('customers.id, customers.name'))
                    ->where('villages.id','=', $village->id)
                    ->whereNull('deleted_at')
                     ->get();
            $result['Sale_agent_id']= $sale_agent_id;
        }
    
        $result['customers'] = $customers;
        if(isset($sale_agent_id))
            $result['sales_agents'] = SalesAgent::where('id','=',$sale_agent_id); 
        else 
            $result['sales_agents'] = SalesAgent::all();

        // DB::table('sales_agents')->get(); 
        $result['products'] =Product::all(); //DB::table('products')->get(); 

        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        
        $emi_collected=EmiCollections::where('bill_id','=',$id)->orderBy('id')->whereNotNull('collect_time')->get(); 
        $result['emi_collected'] = $emi_collected;
        $result['emi_collected_count'] = count($emi_collected);

        $emi_collections=EmiCollections::where('bill_id','=',$id)->whereNull('collect_time')->orderBy('id')->get(); 
        $result['emi_collections'] = $emi_collections;
        $result['emi_collections_count'] = count($emi_collections);
        $result['errMsg'] = $errMsg;

        return view('admin.edit_bill',$result); 
    }

    public function view_bill(Request $request,$id='',$order_id=0)
    {
        if ($id>0) 
        {
            $arr = Bill::where(['id'=>$id])->get();
            //print_r($arr);
            $result['id']= $arr[0]->id;
            $result['bill_date']= $arr[0]->bill_date;
            $result['customer_id']= $arr[0]->customer_id;
            $result['Sale_agent_id']= $arr[0]->Sale_agent_id;
            $result['product_id']= $arr[0]->product_id;
            $result['sale_price']= $arr[0]->sale_price;
            $result['down_payment']= $arr[0]->down_payment;
            $result['EMI']= $arr[0]->EMI;
            $result['EMI_mode']= $arr[0]->EMI_mode;
            $result['EMI_Loan']= $arr[0]->EMI_Loan;
            $result['EMI_Interest']= $arr[0]->EMI_interest;
            $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
            $result['EMI_Period']= $arr[0]->EMI_Period;
            $result['LoanAmount']= $arr[0]->LoanAmount;
            $result['IntOnLoan']= $arr[0]->IntOnLoan;
            $result['status']= $arr[0]->status;
            $result['booking_advance']= $arr[0]->booking_advance;
            $result['balanced_downpayment']= $arr[0]->balanced_downpayment;
            $result['interestPercntage']= $arr[0]->interestPercntage;
            $result['order_id']= $arr[0]->order_id;
            
        }
        //echo '<br>Result <br>';
        //print_r($result);

        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
            $customers = Customer::all();

            if($id==0 && $order_id==0)
            $result['Sale_agent_id']= '';
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;

            $customers = Customer::all();

            $result['Sale_agent_id']= $sale_agent_id;
        }
        
        $result['customers'] = $customers;
        if(isset($sale_agent_id))
            $result['sales_agents'] = SalesAgent::where('id','=',$sale_agent_id); 
        else 
            $result['sales_agents'] = SalesAgent::all();

        $result['products'] =Product::all();

        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        
        $emi_collected=EmiCollections::where('bill_id','=',$id)->orderBy('id')->whereNotNull('collect_time')->get(); 
        $result['emi_collected'] = $emi_collected;
        $result['emi_collected_count'] = count($emi_collected);

        $emi_collections=EmiCollections::where('bill_id','=',$id)->whereNull('collect_time')->orderBy('id')->get(); 
        $result['emi_collections'] = $emi_collections;
        $result['emi_collections_count'] = count($emi_collections);

       return view('admin.view_bill',$result); 
    }

public function print_bill_to_pdf(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Bill::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['bill_date']= $arr[0]->bill_date;
            $result['customer_id']= $arr[0]->customer_id;
        $result['Customer_name']= $arr[0]->getCustomers[0]->name;
            $result['Address']= $arr[0]->getCustomers[0]->address;
            $result['mobile']= $arr[0]->getCustomers[0]->mobile;
              
            //$result['Sale_agent_id']= $arr[0]->Sale_agent_id;
        $result['sale_agent']= $arr[0]->getSalesAgent[0]->name;   
            $result['product_name']= $arr[0]->getProduct[0]->name;

            $result['sale_price']= $arr[0]->sale_price;
            $result['down_payment']= $arr[0]->down_payment;
            $result['EMI']= $arr[0]->EMI;
            //$result['EMI_mode']= $arr[0]->EMI_mode;
            $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
            $result['EMI_Period']= $arr[0]->EMI_Period;
            $result['LoanAmount']= $arr[0]->LoanAmount;
            $result['IntOnLoan']= $arr[0]->IntOnLoan;
            $result['status']= $arr[0]->status;
            $result['booking_advance']= $arr[0]->booking_advance;
            $result['balanced_downpayment']= $arr[0]->balanced_downpayment;
            if($arr[0]->interestPercntage>0)
                $result['interestPercntage']= $arr[0]->interestPercntage;
            else 
                $result['interestPercntage']='';
            
               $result['order_id']= $arr[0]->order_id;
               $result['remarks']= $arr[0]->remarks;   

            $EmiMode=DB::table('calcmode')->find($arr[0]->EMI_mode);
            $result['EMI_mode']=$EmiMode->name;
        $emi_collections=EmiCollections::where('bill_id','=',$id)->orderBy('id')->get(); 
        $result['emi_collections'] = $emi_collections;
        }

        //return view('admin.print_bill',$result); 
        $pdf = PDF::loadView('admin.print_bill', $result);
        return $pdf->download('bill_'.$id.'.pdf');
    }

public function print_bill(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Bill::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['bill_date']= $arr[0]->bill_date;
            $result['customer_id']= $arr[0]->customer_id;
        $result['Customer_name']= $arr[0]->getCustomers[0]->name;
            $result['Address']= $arr[0]->getCustomers[0]->address;
            $result['mobile']= $arr[0]->getCustomers[0]->mobile;
              
            //$result['Sale_agent_id']= $arr[0]->Sale_agent_id;
        $result['sale_agent']= $arr[0]->getSalesAgent[0]->name;   
            $result['product_name']= $arr[0]->getProduct[0]->name;

            $result['sale_price']= $arr[0]->sale_price;
            $result['down_payment']= $arr[0]->down_payment;
            $result['EMI']= $arr[0]->EMI;
            //$result['EMI_mode']= $arr[0]->EMI_mode;
            $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
            $result['EMI_Period']= $arr[0]->EMI_Period;
            $result['LoanAmount']= $arr[0]->LoanAmount;
            $result['IntOnLoan']= $arr[0]->IntOnLoan;
            $result['status']= $arr[0]->status;
            $result['booking_advance']= $arr[0]->booking_advance;
            $result['balanced_downpayment']= $arr[0]->balanced_downpayment;
            if($arr[0]->interestPercntage>0)
                $result['interestPercntage']= $arr[0]->interestPercntage;
            else 
                $result['interestPercntage']='';
            
               $result['order_id']= $arr[0]->order_id;
               $result['remarks']= $arr[0]->remarks;   

            $EmiMode=DB::table('calcmode')->find($arr[0]->EMI_mode);
            $result['EMI_mode']=$EmiMode->name;
        $emi_collections=EmiCollections::where('bill_id','=',$id)->orderBy('id')->get(); 
        $result['emi_collections'] = $emi_collections;
        }

        return view('admin.print_bill_small',$result); 
        //$pdf = PDF::loadView('admin.print_bill', $result);
        //return $pdf->download('bill_'.$id.'.pdf');
    }

    public function manage_bill_process(Request $request)
    {
        
       $request->validate([
        'bill_date'=>'required',
        'customer_id'=>'required',
        'sale_price'=>'required',
        'down_payment'=>'required',
        'EMI_mode'=>'required',
       ]
       ); 
       if ($request->post('id')>0) 
       {
           $model = Bill::find($request->post('id'));

           /////////////
           if($model==null)
           {
            $model=Bill::withTrashed()->find($request->post('id'));
           }
           /////////////
           $msg = 'bill updated';
       }
       else
       {
            $model = new Bill();
            $msg = 'bill Inserted';
       }
       
       $model->bill_date = $request->post('bill_date');
       $model->order_id = $request->post('order_id');
       $model->customer_id = $request->post('customer_id');
       $model->sale_price = $request->post('sale_price');
       $model->Sale_agent_id = $request->post('Sale_agent_id');
       $model->product_id = $request->post('product_id');
       $model->down_payment = $request->post('down_payment');
       $model->EMI = $request->post('EMI');
       $model->EMI_mode = $request->post('EMI_mode');
       $model->EMI_in_Months = $request->post('EMI_in_Months');
       $model->EMI_Period = $request->post('EMI_Period');
       $model->EMI_Loan = $request->post('EMI_Loan');
       $model->EMI_Interest = $request->post('EMI_Interest');
       $model->LoanAmount = $request->post('LoanAmount');
       $model->IntOnLoan = $request->post('IntOnLoan');
       $model->status = $request->post('status');
       $model->booking_advance =$request->post('booking_advance');
    $model->interestPercntage=$request->post('interestPercntage');
    $model->balanced_downpayment=$request->post('balanced_downpayment');
    $model->remarks = $request->post('remarks');
       $model->save();
       $bill_id=$model->id;

       ////////////////
       $branch_id = $request->post('branch_id');
       $product_id = $request->post('product_id');
       $bill_date = $request->post('bill_date');

       if($msg == 'bill Inserted')
        {
        $this->stockUpdate($branch_id,$product_id,$bill_date,2);
        }
       ///////////////////
       $typeName=session()->get('typeName');

       EmiCollections::where('bill_id', $bill_id)->delete();
       
       $emi_collected_count =(int)$request->post('emi_collected_count');
       $EMI_Period=(int)$request->post('EMI_Period');
       
       for($i=1; $i<=$EMI_Period -$emi_collected_count; $i++)
       {
        $model=new EmiCollections();
        //echo '<br> bill id '.$bill_id;
        $model->bill_id=$bill_id;
        $model->emi_date=$request->post('EMI_Date'.$i);
        $model->emi_amount=$request->post('EMI');
        $model->EMI_Loan=$request->post('EMI_Loan');
        $model->EMI_interest=$request->post('EMI_Interest');
        $model->paid_amt=0;
        $model->due_amt=0;
        $model->collect_by=0;
        $model->recieve_by=0;
        $model->save();
       }
       return redirect($typeName.'/bills');
    }

   public function delete(Request $request,$id)
    {
       $typeName=session()->get('typeName');
       $message='';
       $emis=EmiCollections::where('bill_id','=',$id)->whereNotNull('collect_time')->get();
       $c=count($emis);
       if($c>0)
       {
            $message = $c.' EMI(s) already colleted ';
       }

       if($message =='')
       {
       
       $model = Bill::find($id);
       $model->delete();
       $branch_id=1; // default
       $product_id=$model->product_id;
       $bill_date = $model->bill_date;

       $this->stockUpdate($branch_id,$product_id,$bill_date,3);
       EmiCollections::where('bill_id', $id)->delete();
       $request->session()->flash('message','bill deleted');
        return redirect($typeName.'/bills')->with('message','Bill deleted'); 
       
       }
       else 
       {
        return redirect($typeName.'/bills')->with('error','Unable to delete as '.$message.' linked with this bill');
       }
    
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Bill::withTrashed()->find($id);
       $model->forceDelete();
       EmiCollections::where('bill_id', $id)->forceDelete();
       $request->session()->flash('message','bill permantly deleted');
       return redirect('admin/bill/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Bill::withTrashed()->find($id);
       $model->restore();

       $branch_id=1; // default
       $product_id=$model->product_id;
       $bill_date = $model->bill_date;

       $this->stockUpdate($branch_id,$product_id,$bill_date,4);

    EmiCollections::where('bill_id', $id)->restore();
       $request->session()->flash('message','Bill Restored');
       return redirect('admin/bill/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Bill::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','bill status changed');
       return redirect('admin/bills');
    }

    public function final_submit(Request $request,$final_submit,$id)
    {
       
       $model = Bill::find($id);
       $model->final_submit = $final_submit;
       $model->save();
       $request->session()->flash('message','bill final_submit changed');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/bills');
    }
    public function export(Excel $excel)
    {
        return $excel->download(new BillExport,'bills.xlsx');
    }

    public function stockUpdate($branch_id,$product_id,$bill_date,$action)
    {

$query = DB::select( DB::raw("SELECT * FROM `branch_stocks` 
    WHERE `branch_id` = ".$branch_id." AND `product_id` =".$product_id));
   $id=0;

       if ($query !=null) 
        {
            $id=$query[0]->id;
            $branchStock = BranchStock::find($id);
            $msg = 'Branch Stock Updated';
       
        
       $stock=(int)$branchStock->stock;
       $qty=1;      // when Bill Created or restored action 2 or 4
       if($action=='2' || $action=='4') 
         $stock -= $qty ;
       else if($action=='3') // when Bill deleted
         $stock += $qty ;

       $branchStock->stock = $stock;
       
       $branchStock->save();

       ////////////////////////
        $branchStockDetails = new BranchStockDetails();
        $branchStockDetails->branch_id= $branch_id;
        $branchStockDetails->product_id= $product_id;
        $branchStockDetails->txn_date= $bill_date;
        $branchStockDetails->qty= 1;
        $branchStockDetails->current_stock =$stock;
        $branchStockDetails->mode = $action; // sale 
        $branchStockDetails->refer_id=0; // not applicable
        $branchStockDetails->save();
        }
        else 
        {
           $msg="Branch stock not updated"; 
        }

    //////////////////////////
        session()->flash('message',$msg);
      
    }
}
