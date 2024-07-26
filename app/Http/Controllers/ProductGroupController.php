<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Product_sub_group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductGroupController extends Controller
{
    public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$productGroup = ProductGroup::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()->simplepaginate(20);

        }
        else
        {   
            $productGroup  = ProductGroup::latest()->simplepaginate(20);
        }

        $loan_types= DB::table('loan_types')->get(); 
        $result = compact('productGroup','search','loan_types');
        return view('admin.productGroup',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   ProductGroup::onlyTrashed()->get();
        return view('admin.productGroup-trash',$result); 
    }

    public function edit_product_group(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = ProductGroup::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            $result['loan_type']= $arr[0]->loan_type;
            $result['status']= $arr[0]->status;
        }
        else
        {
            $result['id']='0';
            $result['name']='';
            $result['loan_type']='';
            $result['status']='1';
        }
        //print_r($result);
        $result['loan_types'] = DB::table('loan_types')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
       return view('admin.edit_ProductGroup',$result); 
    }

    public function manage_product_group_process(Request $request)
    {
        
       $request->validate([
        'name'=>'required|unique:product_groups,name,'.$request->post('id'),
        'loan_type'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = ProductGroup::find($request->post('id'));
           $msg = 'ProductGroup updated';
       }
       else
       {
            $model = new ProductGroup();
            $msg = 'ProductGroup Inserted';
       }
       
       $model->name = $request->post('name');
       $model->loan_type = $request->post('loan_type');
       $model->status = $request->post('status');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/product_group');
    }

   public function delete(Request $request,$id)
    {
       
       $message='';

       $products=Product::where('GroupId','=',$id)->get();
       $c=count($products);
       if($c>0)
       {
            $message = $c.' Product(s) ';
       }


       $product_sub_groups=Product_sub_group::where('prod_group_id','=',$id)->get();
       $c=count($product_sub_groups);
       if($c>0)
       {
            $message .= ' and '.$c.' Product Sub Group(s) ';
       }

       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = ProductGroup::find($id);
        $model->delete();
        return redirect($typeName.'/product_group')->with('message','Product Group deleted'); 
       }
       else 
       {
        return redirect($typeName.'/product_group')->with('error','Unable to delete as '.$message.' linked with this Product Group');
       }

    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = ProductGroup::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Product Group permantly deleted');
       return redirect('admin/product_group/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = ProductGroup::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Product Group Restored');
       return redirect('admin/product_group/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = ProductGroup::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','ProductGroup status changed');
       return redirect('admin/product_group');
    }
}
