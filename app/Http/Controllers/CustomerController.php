<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Bill;
use App\Models\Admin;
use App\Models\Village;
use App\Models\VillageAgent;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
     public function index(Request $request)
    {
        $id=$request['id'] ?? "";
        $name=$request['name'] ?? "";
        $mobile=$request['mobile'] ?? "";
        $village_id=$request['village_id'] ?? array();
        $status=$request['status'] ?? "";
        $rows=$request['rows'] ?? "15";
    /////////
    $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
        $Villages = Village::all();     
        $village_id1=array();
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
        $Villages = Village::all();
        $village_id1=array();
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

        $Villages = Village::where('id',$villageAgent->village_id)->get();     
            //echo "<br>(village id ".$village_id.'<br>';
            if(empty($village_id))
            {
              //  echo '**'.count($Villages);
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
        }
    /////////
    if($id !='' || $name !="" || $mobile !='' || isset($village_id[0]) !='' || $status !='')
        {
//echo 'search status '.$status;
//DB::enableQueryLog();
        $data = $request->all();
        if(!empty($village_id1))
            $data['village_id']=$village_id1;

$customers =  Customer::select('id', 'name', 'mobile', 'address', 'pin', 'village_id', 'AddressProofImage', 'AddressProofType', 'IdProofImage', 'IdProofType', 'isApproved', 'status')
->when(!empty($data['id']) !='' , function ($query) use($data){
return $query->where('id', '=', $data['id']);
})
->when(!empty($data['name']) , function ($query) use($data){
return $query->where('name', 'LIKE', '%'.$data['name'].'%');
})
->when (!empty($data['mobile']) , function ($query) use($data){
return $query->where('mobile',$data['mobile']);
})
->when (!empty($data['village_id']) , function ($query) use($data){
return $query->whereIn('village_id', $data['village_id']);
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
//     $customers  = Customer::latest()->simplepaginate(10);
$customers =  Customer::select('id', 'name', 'mobile', 'address', 'pin', 'village_id', 'AddressProofImage', 'AddressProofType', 'IdProofImage', 'IdProofType', 'isApproved', 'status')
->when (!empty($village_id1) , function ($query) use($village_id1){
return $query->whereIn('village_id', $village_id1);
})
->latest()->simplepaginate($rows);     

        $displaySearch='display:none;';
        $a_search_text='Show Search';
        }
        
        $id_proof_types = DB::table('id_proof_types')->get(); 
        $address_proof_types = DB::table('address_proof_types')->get(); 
        $statuses = DB::table('status')->get(); 
                
        $result = compact('customers','Villages','statuses','name','mobile','village_id','status','id_proof_types','address_proof_types','id','displaySearch','a_search_text','rows');
        //echo '<pre>';
        //print_r($result);
        return view('admin.customer',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   Customer::onlyTrashed()->get();
        $result['Villages'] = DB::table('villages')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        $result['id_proof_types'] = DB::table('id_proof_types')->get(); 
        $result['address_proof_types'] = DB::table('address_proof_types')->get(); 
       return view('admin.customer-trash',$result); 
    }

    public function edit_customer(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Customer::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            $result['village_id']= $arr[0]->village_id;
            $result['mobile']= $arr[0]->mobile;
            $result['address']= $arr[0]->address;
            $result['pin']= $arr[0]->pin;
            $result['IdProofType']= $arr[0]->IdProofType;
            $result['IdProofImage']= $arr[0]->IdProofImage;
            $result['IDProofImagePath']= $arr[0]->IDProofImagePath();
            $result['AddressProofType']= $arr[0]->AddressProofType;
            $result['AddressProofImage']= $arr[0]->AddressProofImage;
            $result['AddressProofImagePath']= $arr[0]->AddressProofImagePath();
            $result['isApproved']= $arr[0]->isApproved;
            $result['status']= $arr[0]->status;
        }
        else
        {
            $result['id']= '0';
            $result['name']= '';
            $result['village_id']= '';
            $result['mobile']= '';
            $result['address']= '';
            $result['pin']= '';
            $result['IdProofType']= '0';
            $result['IdProofImage']= '';
        $result['IDProofImagePath']='/storage/media/NoImage.png';
            $result['AddressProofType']= '0';
            $result['AddressProofImage']= '';
        $result['AddressProofImagePath']='/storage/media/NoImage.png';
            $result['isApproved']= '0';
            $result['status']= '1';
        }
        
        //$result['Villages'] = DB::table('villages')->get(); 

        $admin_id=session()->get('ADMIN_ID');      
        $admin=Admin::find($admin_id);
        if(session()->get('ADMIN_TYPE')=='1') // if admin
        {
            $result['Villages'] = DB::table('villages')->get();    
        }
        else if(session()->get('ADMIN_TYPE')=='3') // if sale agent
        {
            $sale_agent_id=$admin->refer_id;
            $result['Villages'] = Village::all();     
        }
        else if(session()->get('ADMIN_TYPE')=='4') // if village
        {
            $village_agent_id=$admin->refer_id;
            $villageAgent=VillageAgent::find($village_agent_id);

        $result['Villages'] = DB::table('villages')->where('id',$villageAgent->village_id)->get();     
            //echo "<br>(village id ".$village_id.'<br>';
        }
    
        $result['statuses'] = DB::table('status')->get(); 
        $result['id_proof_types'] = DB::table('id_proof_types')->get(); 
        $result['address_proof_types'] = DB::table('address_proof_types')->get(); 
       
       return view('admin.edit_Customer',$result); 
    }

    public function manage_customer_process(Request $request)
    {
    
        $image_validation='';
        $IdProofImage_name='';
        $AddressProofImage_name='';

       if ($request->post('id')>0) 
       {
            $image_validation="mimes:jpeg,jpeg,png,gif";
       }
       else
       {
            $image_validation="required|mimes:jpeg,jpeg,png,gif";    
       } 
       $request->validate([
        'mobile'=>'required|unique:customers,mobile,'.$request->post('id'),
        'name'=>'required',
        'address'=>'required',
        'village_id'=>'required',
        'IdProofType'=>'required',
        'IdProofImage'=>$image_validation,
        'AddressProofType'=>'required',
        'AddressProofImage' => $image_validation,
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = Customer::find($request->post('id'));
           $msg = 'Customer updated';
       }
       else
       {
            $model = new Customer();
            $msg = 'Customer Inserted';
       }
        
        if($request->hasfile('IdProofImage'))
       {
            $ext=$request->file('IdProofImage')->getClientOriginalExtension();
            $IdProofImage_name =  'cust_'.$request->post('name').'_idProof_'.time().'.'.$ext;
            $request->file('IdProofImage')->storeAs('/media/',$IdProofImage_name);
            
        }
        else
        {
            $IdProofImage_name=$request->post('hdIdProofImage');
        }

         if($request->hasfile('AddressProofImage'))
       {
            $ext=$request->file('AddressProofImage')->getClientOriginalExtension();
            $AddressProofImage_name = 'cust_'.$request->post('name').'_adProof_'.time().'.'.$ext;
            $request->file('AddressProofImage')->storeAs('/media/',$AddressProofImage_name);
            
        }
        else 
        {
            $AddressProofImage_name=$request->post('hdAddressProofImage');
        }
       $model->name = $request->post('name');
       $model->village_id = $request->post('village_id');
       $model->mobile = $request->post('mobile');
       $model->address = $request->post('address');
       $model->pin = $request->post('pin');
       $model->IdProofType = $request->post('IdProofType');
       $model->IdProofImage = $IdProofImage_name;
       $model->AddressProofType = $request->post('AddressProofType');
       $model->AddressProofImage = $AddressProofImage_name;
       $model->isApproved = $request->post('isApproved');
       $model->status = $request->post('status');
       $model->save();
       $request->session()->flash('message',$msg);
       $typeName=session()->get('typeName');
       return redirect($typeName.'/customer');
    }

   public function delete(Request $request,$id)
    {
        $message='';

       $orders=Order::where('customer_id','=',$id)->get();
       $c=count($orders);
       if($c>0)
       {
            $message = $c.' Order(s) ';
       }


       $bills =Bill::where('customer_id','=',$id)->get();

       $c=count($bills);
       if($c>0)
       {
            $message .=' and '.$c.' Bill(s) ';
       }
       
       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = Customer::find($id);
        $model->delete();
        //$request->session()->flash('message','Customer deleted');
        return redirect($typeName.'/customer')->with('message','Customer deleted'); 
       }
       else 
       {
        return redirect($typeName.'/customer')->with('error','Unable to delete as '.$message.' linked with this Customer');
       }
       
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Customer::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Customer permantly deleted');

       $typeName=session()->get('typeName');
       return redirect($typeName.'/customer/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Customer::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Customer Restored');

       $typeName=session()->get('typeName');
       return redirect($typeName.'/customer/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Customer::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Customer status changed');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/customer');
    }

    public function approve(Request $request,$approve,$id)
    {
       
       $model = Customer::find($id);
       $model->isApproved = $approve;
       $model->save();
       $request->session()->flash('message','Customer approve status changed');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/customer');
    }
}
