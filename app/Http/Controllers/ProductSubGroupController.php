<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Product_sub_group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSubGroupController extends Controller
{
    public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$product_sub_group = Product_sub_group::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()->simplepaginate(10);

        }
        else
        {   
        $product_sub_group  = Product_sub_group::latest()->simplepaginate(10);
        }

        $product_groups = DB::table('product_groups')->get();
        $result = compact('product_sub_group','search','product_groups');
        return view('admin.productSubGroup',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   Product_sub_group::onlyTrashed()->get();
        return view('admin.productSubGroup-trash',$result); 
    }

public function edit_product_sub_group(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Product_sub_group::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            $result['prod_group_id']= $arr[0]->prod_group_id;
            $result['status']= $arr[0]->status;
        }
        else
        {
            $result['id']='0';
            $result['name']='';
            $result['prod_group_id']='';
            $result['status']='1';
        }
    $result['product_groups'] = DB::table('product_groups')->get(); 
    $result['statuses'] = DB::table('status')->get(); 
    return view('admin.edit_ProductSubGroup',$result); 
    }

    public function manage_product_sub_group_process(Request $request)
    {
        
       $request->validate([
        'name'=>'required|unique:product_sub_groups,name,'.$request->post('id'),
        'prod_group_id'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = Product_sub_group::find($request->post('id'));
           $msg = 'Product_sub_group updated';
       }
       else
       {
            $model = new Product_sub_group();
            $msg = 'Product_sub_group Inserted';
       }
       
       $model->name = $request->post('name');
       $model->prod_group_id = $request->post('prod_group_id');
       $model->status = $request->post('status');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/product_sub_group');
    }

   public function delete(Request $request,$id)
    {
        
       $message='';

       $products=Product::where('SubGroupId','=',$id)->get();
       $c=count($products);
       if($c>0)
       {
            $message = $c.' Product(s) ';
       }

       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = Product_sub_group::find($id);
        $model->delete();
        return redirect($typeName.'/product_sub_group')->with('message','Product Sub Group deleted'); 
       }
       else 
       {
        return redirect($typeName.'/product_sub_group')->with('error','Unable to delete as '.$message.' linked with this Product Sub Group');
       }
    
    }

    public function forceDelete(Request $request,$id)
    {
           
       $model = Product_sub_group::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Product Sub Group permantly deleted');
       return redirect('admin/product_sub_group/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Product_sub_group::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Product Sub Group Restored');
       return redirect('admin/product_sub_group/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Product_sub_group::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Product_sub_group status changed');
       return redirect('admin/product_sub_group');
    }
}
