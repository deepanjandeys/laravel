<?php
namespace App\Http\Controllers;

use App\Models\Customer;
//use App\Models\SalesAgent;
use App\Models\VillageAgent;
use App\Models\AddressProofType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressProofTypeController extends Controller
{
   public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$addressProofType = AddressProofType::where(function ($query) use ($search){
                $query->where('AddressProofType', 'like', '%'.$search.'%');
            })
            ->latest()->simplepaginate(10);
        }
        else
        {   
    $addressProofType  = AddressProofType::latest()->simplepaginate(10);
        }
        //$statuses = DB::table('status')->get();
        $result = compact('addressProofType','search');
        return view('admin.addressProofType',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   AddressProofType::onlyTrashed()->get();
        return view('admin.addressProofType-trash',$result); 
    }

    public function edit_addressProofType(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = AddressProofType::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['AddressProofType']= $arr[0]->AddressProofType;
        }
        else
        {
            $result['id']='0';
            $result['AddressProofType']='';
        }
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.edit_AddressProofType',$result); 
    }

    public function manage_addressProofType_process(Request $request)
    {
        
       $request->validate([
        'AddressProofType'=>'required|unique:address_proof_types,AddressProofType,'.$request->post('id'),
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = AddressProofType::find($request->post('id'));
           $msg = 'AddressProofType updated';
       }
       else
       {
            $model = new AddressProofType();
            $msg = 'AddressProofType Inserted';
       }
       
       $model->AddressProofType = $request->post('AddressProofType');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/addressProofType');
    }

   public function delete(Request $request,$id)
    {
             $message='';

       $customers=Customer::where('AddressProofType','=',$id)->get();
       $c=count($customers);
       if($c>0)
       {
            $message = $c.' Customer(s) ';
       }

       $villageAgents =VillageAgent::where('AddressProofType','=',$id)->get();

       $c=count($villageAgents);
       if($c>0)
       {
            $message .=' and '.$c.' Village Agent(s) ';
       }
       
/*
       $salesAgents =SalesAgent::where('AddressProofType','=',$id)->get();

       $c=count($salesAgents);
       if($c>0)
       {
            $message .=' and '.$c.' Sale Agents(s) ';
       }
  */     
       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = AddressProofType::find($id);
        $model->delete();
        return redirect($typeName.'/addressProofType')->with('message','AddressProofType deleted'); 
       }
       else 
       {
        return redirect($typeName.'/addressProofType')->with('error','Unable to delete as '.$message.' linked with this AddressProofType');
       }

       
       $model = AddressProofType::find($id);
       $model->delete();
       $request->session()->flash('message','AddressProofType deleted');
       return redirect('admin/addressProofType');
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = AddressProofType::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','AddressProofType permantly deleted');
       return redirect('admin/addressProofType/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = AddressProofType::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','AddressProofType Restored');
       return redirect('admin/addressProofType/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = AddressProofType::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','AddressProofType status changed');
       return redirect('admin/AddressProofType');
    }
}
