<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
 public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
        $Branch = Branch::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%')->orwhere('pin','like',"%$search%")->orwhere('address','like',"%$search%");
            })
            ->latest()->simplepaginate(10);
        }
        else
        {   
    $Branch  = Branch::latest()->simplepaginate(10);
        }
        //$statuses = DB::table('status')->get();
        $result = compact('Branch','search');
        return view('admin.branch',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   Branch::onlyTrashed()->get();
        return view('admin.branch-trash',$result); 
    }

    public function edit_branch(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Branch::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            $result['address']= $arr[0]->address;
            $result['pin']= $arr[0]->pin;
            $result['mobile']= $arr[0]->mobile;
            $result['status']= $arr[0]->status;
        }
        else
        {
            $result['id']='0';
            $result['name']='';
            $result['address']= '';
            $result['pin']= '';
            $result['mobile']= '';
            $result['status']= '1';
        }
        //print_r($result);
        $result['statuses'] = DB::table('status')->get(); 
       return view('admin.edit_Branch',$result); 
    }

    public function manage_branch_process(Request $request)
    {
        
       $request->validate([
        'name'=>'required|unique:branches,name,'.$request->post('id'),
        'pin'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = Branch::find($request->post('id'));
           $msg = 'Branch updated';
       }
       else
       {
            $model = new Branch();
            $msg = 'Branch Inserted';
       }
       
       $model->name = $request->post('name');
       $model->address = $request->post('address');
       $model->pin = $request->post('pin');
       $model->mobile = $request->post('mobile');
       $model->status = $request->post('status');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/branch');
    }

   public function delete(Request $request,$id)
    {
       
       $model = Branch::find($id);
       $model->delete();
       $request->session()->flash('message','Branch deleted');
       return redirect('admin/branch');
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Branch::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Branch permantly deleted');
       return redirect('admin/branch/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Branch::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Branch Restored');
       return redirect('admin/branch/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Branch::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Branch status changed');
       return redirect('admin/Branch');
    }
}
