<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Admin;
use App\Models\Product;
use App\Models\VillageAgent;
use App\Models\Village;
use App\Models\Customer;
use App\Models\SalesAgent;
use App\Models\BranchStock;
use App\Models\BranchStockDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
class OrderController extends Controller
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
    //$village_id1=array();
    $Villages = Village::all();     
$SalesAgents =SalesAgent::where('id','=',$sale_agent_id)->get();
$sale_agent_id1[]=$sale_agent_id;
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

$Villages = Village::where('id',$villageAgent->village_id)->get(); 
            if(empty($village_ids))
            {
                if(count($Villages)>0)
                {
                    foreach($Villages as $v)
                    {
                    $village_id1[]=$v->id;
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
//////////////////////////////////////////////////////
if($id !='' || $cust_name !="" || $cust_mobile !='' || isset($village_ids[0]) || isset($sale_agent_ids[0]) || isset($product_ids[0]) || $status !='')
        {
//DB::enableQueryLog();
        $data = $request->all();
        $Products=Product::all();

        if(empty($product_ids))
            {
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

$orders =  Order::select( 'orders.id','bills.id as bill_id','order_date', 'orders.customer_id', 'orders.Sale_agent_id', 'orders.product_id', 'orders.sale_price', 'orders.down_payment','orders.EMI_Loan','orders.EMI_Interest', 'orders.EMI', 'orders.EMI_mode', 'orders.EMI_in_Months', 'orders.EMI_Period', 'orders.LoanAmount', 'orders.interestPercntage', 'orders.IntOnLoan', 'orders.status', 'orders.booking_advance')
->leftJoin('bills','bills.order_id','=','orders.id')->when(!empty($data['id'])  , function ($query) use($data){
return $query->where('orders.id', '=', $data['id']);
})
->when($Date_from !='' && $Date_to !='' , function ($query) use($Date_from,$Date_to){
return $query->whereBetween('orders.order_date', [$Date_from, $Date_to]);
})
->when (!empty($customer_ids) , function ($query) use($customer_ids)
{
return $query->whereIn('orders.customer_id', $customer_ids);
})
->when (!empty($product_ids) , function ($query) use($product_ids)
{
return $query->whereIn('orders.product_id', $product_ids);
})
->when (!empty($sale_agent_ids) , function ($query) use($sale_agent_ids)
{
return $query->whereIn('orders.sale_agent_id', $sale_agent_ids);
})
->when ($status !='' , function ($query) use($data){
return $query->where('orders.status', '=', $data['status']);
})
->latest('orders.created_at')->simplepaginate($rows);     
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
//DB::enableQueryLog();
$orders =    Order::select( 'orders.id','bills.id as bill_id','order_date', 'orders.customer_id', 'orders.Sale_agent_id', 'orders.product_id', 'orders.sale_price', 'orders.down_payment', 'orders.EMI_Loan','orders.EMI_Interest', 'orders.EMI', 'orders.EMI_mode', 'orders.EMI_in_Months', 'orders.EMI_Period', 'orders.LoanAmount', 'orders.interestPercntage', 'orders.IntOnLoan', 'orders.status', 'orders.booking_advance')->leftJoin('bills','bills.order_id','=','orders.id')->when (!empty($customer_ids) , function ($query) use($customer_ids)
{
return $query->whereIn('orders.customer_id', $customer_ids);
})->latest('orders.created_at')->simplepaginate($rows);     
//print_r(DB::getQueryLog());
//echo '<pre>';
//print_r($orders);

        $displaySearch='display:none;';
        $a_search_text='Show Search';
    }
        
        //$id_proof_types = DB::table('id_proof_types')->get(); 
        ///$address_proof_types = DB::table('address_proof_types')->get(); 
        $statuses = DB::table('status')->get(); 
        
    $calcmode = DB::table('calcmode')->get(); 
    $result = compact('id','orders','Villages','village_ids','statuses','status','SalesAgents','sale_agent_ids','customers','Products','product_ids','calcmode','displaySearch','a_search_text','cust_name','cust_mobile','Date_From','Date_To','rows');
        $result['sumAdvBooking']=number_format(Order::sum('booking_advance'),2);
        return view('admin.order',$result); 
    }
    public function trash()
    {
        $result['data'] =   Order::onlyTrashed()->get();
        $result['customers'] = DB::table('customers')->get(); 
        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.order-trash',$result); 
    }

    public function edit_order(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Order::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['order_date']= $arr[0]->order_date;
            $result['customer_id']= $arr[0]->customer_id;
            $result['Sale_agent_id']= $arr[0]->Sale_agent_id;
            $result['product_id']= $arr[0]->product_id;
            $result['sale_price']= $arr[0]->sale_price;
            $result['down_payment']= $arr[0]->down_payment;
            $result['EMI_Loan']= $arr[0]->EMI_Loan;
            $result['EMI_Interest']= $arr[0]->EMI_Interest;
            $result['EMI']= $arr[0]->EMI;
            $result['EMI_mode']= $arr[0]->EMI_mode;
            $result['EMI_in_Months']= $arr[0]->EMI_in_Months;
            $result['EMI_Period']= $arr[0]->EMI_Period;
            $result['LoanAmount']= $arr[0]->LoanAmount;
            $result['IntOnLoan']= $arr[0]->IntOnLoan;
            $result['interestPercntage']= $arr[0]->interestPercntage;
            $result['status']= $arr[0]->status;
            $result['booking_advance']= $arr[0]->booking_advance;
            $result['ADMIN_TYPE'] = session()->get('ADMIN_TYPE');
        }
        else
        {
            $result['id']= '0';
            $result['order_date']= date('d-m-Y');
            $result['customer_id']= '';
            $result['Sale_agent_id']= '';
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
            $result['interestPercntage']= 0;
            $result['booking_advance']= 0;
            $result['ADMIN_TYPE'] = session()->get('ADMIN_TYPE');
        }
        //print_r($result);
        //$result['customers'] = Customer::all(); 
        // DB::table('customers')->get(); 
        /////////////////////
        ///////////////////
        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
            $customers = DB::table('customers')->get();
            if($id==0)
            $result['Sale_agent_id']= '';
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $customers = DB::table('customers')->get();
/*
            $customers = DB::table('customers')
        ->join('villages', 'villages.id', '=', 'customers.village_id')
    ->select(DB::raw('customers.id, customers.name'))
                    ->where('villages.sale_agent','=', $sale_agent_id)
                     ->get();
    */
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
                     ->get();
            $result['Sale_agent_id']= $sale_agent_id;
        }
        $result['customers'] = $customers; 
        //////////////////////
        $result['sales_agents'] = SalesAgent::all(); // DB::table('sales_agents')->get(); 
        $result['products'] =Product::all(); //DB::table('products')->get(); 

        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 

        //print_r($result);

       return view('admin.edit_Order',$result); 
    }
public function place_order(Request $request,$sale_agent_id='',$product_id='')
    {
        $result['id']= '0';
        $result['order_date']= date('d-m-Y');
        $result['customer_id']= '';
        $result['Sale_agent_id']= $sale_agent_id;
        $result['product_id']= $product_id;
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
        $result['interestPercntage']= 0;
        ////////////////////////////

        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
            $customers = Customer::all();
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $customers = Customer::all();
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

      $customers = DB::table('customers')
        ->join('villages', 'villages.id', '=', 'customers.village_id')
    ->select(DB::raw('customers.id, customers.name'))
                    ->where('villages.id','=', $villageAgent->village_id)
                     ->get();
        }
        $result['customers'] = $customers; 
        //////////////////////

        $result['sales_agents'] = SalesAgent::all(); 
        $result['products'] =Product::all(); 
        $result['calcmode'] = DB::table('calcmode')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.place_Order',$result); 
    }

    public function manage_order_process(Request $request)
    {
        
       $request->validate([
        'order_date'=>'required',
        'customer_id'=>'required',
        'sale_price'=>'required',
        'down_payment'=>'required',
        'EMI_mode'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = Order::find($request->post('id'));
           $msg = 'Order updated';
       }
       else
       {
            $model = new Order();
            $msg = 'Order Inserted';
       }
       
       $model->order_date = $request->post('order_date');
       $model->customer_id = $request->post('customer_id');
       $model->sale_price = $request->post('sale_price');
       $model->Sale_agent_id = $request->post('Sale_agent_id');
       $model->product_id = $request->post('product_id');
       $model->down_payment = $request->post('down_payment');
       $model->EMI_Loan = $request->post('EMI_Loan');
       $model->EMI_Interest = $request->post('EMI_Interest');     
       $model->EMI = $request->post('EMI');
       $model->EMI_mode = $request->post('EMI_mode');
       $model->EMI_in_Months = $request->post('EMI_in_Months');;
       $model->EMI_Period = $request->post('EMI_Period');
       $model->LoanAmount = $request->post('LoanAmount');
    $model->interestPercntage = $request->post('interestPercntage');
       $model->IntOnLoan = $request->post('IntOnLoan');
       $model->status = $request->post('status');
       $model->booking_advance =$request->post('booking_advance');
       $model->save();

       /////////////////////////////////////////////
       $request->session()->flash('message',$msg);
       $typeName=session()->get('typeName');
       return redirect($typeName.'/orders');
    }

   public function delete(Request $request,$id)
    {
       
       $model = Order::find($id);
       $model->delete();
       $request->session()->flash('message','Order deleted');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/orders');
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Order::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Order permantly deleted');
       return redirect('admin/order/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Order::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Order Restored');
       return redirect('admin/order/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Order::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Order status changed');
       return redirect('admin/order');
    }
}
