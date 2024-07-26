<?php

namespace App\Http\Controllers;

use App\Models\Customer;
//use App\Models\SalesAgent;
use App\Models\VillageAgent;
use App\Models\IdProofType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IdProofTypeController extends Controller
{
     public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$IdProofType = IdProofType::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()->simplepaginate(10);
        }
        else
        {   
$IdProofType  = IdProofType::latest()->simplepaginate(10);
        }
        $result = compact('IdProofType','search');
        return view('admin.idProofType',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   IdProofType::onlyTrashed()->get();
        return view('admin.idProofType-trash',$result); 
    }

    public function edit_idProofType(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = IdProofType::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
        }
        else
        {
            $result['id']='0';
            $result['name']='';
        }
       return view('admin.edit_IdProofType',$result); 
    }

    public function manage_idProofType_process(Request $request)
    {
        
       $request->validate([
        'name'=>'required|unique:id_proof_types,name,'.$request->post('id'),
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = IdProofType::find($request->post('id'));
           $msg = 'IdProofType updated';
       }
       else
       {
            $model = new IdProofType();
            $msg = 'IdProofType Inserted';
       }
       
       $model->name = $request->post('name');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/idProofType');
    }

   public function delete(Request $request,$id)
    {
       $message='';

       $customers=Customer::where('IdProofType','=',$id)->get();
       $c=count($customers);
       if($c>0)
       {
            $message = $c.' Customer(s) ';
       }

       $villageAgents =VillageAgent::where('IdProofType','=',$id)->get();

       $c=count($villageAgents);
       if($c>0)
       {
            $message .=' and '.$c.' Village Agent(s) ';
       }
       
/*
       $salesAgents =SalesAgent::where('IdProofType','=',$id)->get();

       $c=count($salesAgents);
       if($c>0)
       {
            $message .=' and '.$c.' Sale Agents(s) ';
       }
  */     
       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = IdProofType::find($id);
        $model->delete();
        return redirect($typeName.'/idProofType')->with('message','IdProofType deleted'); 
       }
       else 
       {
        return redirect($typeName.'/idProofType')->with('error','Unable to delete as '.$message.' linked with this IdProofType');
       }

    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = IdProofType::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','IdProofType permantly deleted');
       return redirect('admin/idProofType/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = IdProofType::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','IdProofType Restored');
       return redirect('admin/idProofType/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = IdProofType::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','IdProofType status changed');
       return redirect('admin/IdProofType');
    }
}
