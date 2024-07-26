<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Bill;
use App\Models\Product;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$product = Product::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%')->orwhere('MRP','like',"%$search%");
            })
            ->latest()->simplepaginate(10);
        }
        else
        {   
     $product  = Product::latest()->simplepaginate(5);
        }
        $Groups = DB::table('product_groups')->get(); 
        $subGroups = DB::table('product_sub_groups')->get(); 
        $result = compact('product','search','Groups','subGroups');
       
        return view('admin.product',$result); 
    }
    public function product_list(Request $request)
    {
        $id=session()->get('ADMIN_ID'); // user id
        $admin=Admin::find($id);
        $sale_agent_id=$admin->refer_id;

        $search=$request['search'] ?? "";
        if($search !="")
        {
$product = Product::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%')->orwhere('MRP','like',"%$search%");
            })
            ->latest()->simplepaginate(5);
        }
        else
        {   
     $product  = Product::latest()->simplepaginate(5);
        }
        $Groups = DB::table('product_groups')->get(); 
        $subGroups = DB::table('product_sub_groups')->get(); 
        $result = compact('product','search','Groups','subGroups','sale_agent_id');
        return view('admin.product_sale_agent',$result); 
    }
    
     public function trash()
    {
        $result['data'] =   Product::onlyTrashed()->get();
        $result['Groups'] = DB::table('product_groups')->get(); 
        $result['subGroups'] = DB::table('product_sub_groups')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.product-trash',$result); 
    }

    public function edit_product(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Product::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['code']= $arr[0]->code;
            $result['name']= $arr[0]->name;
            $result['description']= $arr[0]->description;
            $result['GroupId']= $arr[0]->GroupId;
            $result['SubGroupId']= $arr[0]->SubGroupId;
            $result['MRP']= $arr[0]->MRP;
            $result['image']= $arr[0]->image;
            $result['status']= $arr[0]->status;
            $result['imagePath']= $arr[0]->ImagePath();
        }
        else
        {
        $code='P000001';
        $query = DB::select( DB::raw("SELECT max(`id`) as mxId FROM `products`"));

       if($query !=null)
        {
        $code= 'P'.str_pad($query[0]->mxId, 6, '0',STR_PAD_LEFT);
        }
            $result['id']='0';
            $result['code']= $code;
            $result['name']= '';
            $result['description']= '';
            $result['GroupId']= '';
            $result['SubGroupId']= '';
            $result['MRP']= '';
            $result['image']= '';
            $result['status']= '1';
            $result['imagePath']= '/storage/media/NoImage.png';
        }
        //print_r($result);
        $result['Groups'] = DB::table('product_groups')->get(); 
        $result['subGroups'] = DB::table('product_sub_groups')->get(); 
        $result['statuses'] = DB::table('status')->get(); 
       return view('admin.edit_Product',$result); 
    }

    public function manage_product_process(Request $request)
    {
       //$image_validation='';
       $image_name='';
       //if ($request->post('id')>0) 
       {
            $image_validation="mimes:jpeg,jpeg,png,gif";
       }
       /*else
       {
            $image_validation="required|mimes:jpeg,jpeg,png,gif";    
       } */

       $request->validate([
        'code'=>'required|unique:products,code,'.$request->post('id'),
        'name'=>'required',
        'MRP'=>'required',
        'image'=>$image_validation,
        'GroupId'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = Product::find($request->post('id'));
           $msg = 'Product updated';
       }
       else
       {
            $model = new Product();
            $msg = 'Product Inserted';
       }
       
       if($request->hasfile('image'))
       {
       
            /*
            $image = $request->file('image');
            $ext = $image->extension();
            $image_name = time().'.'.$ext;
            $image->storeAs('/public/media',$image_name);
            */
            
            $ext=$request->file('image')->getClientOriginalExtension();
            $image_name = 'prod_'.$request->post('name').'_'.time().'.'.$ext;
            $request->file('image')->storeAs('/media/',$image_name);
            
        }
        else 
        {
            $image_name=$request->post('hdImage');
        }
        
       $model->name = $request->post('name');
       $model->description = $request->post('description');
       $model->GroupId = $request->post('GroupId');
       $model->SubGroupId = $request->post('SubGroupId');
       $model->code = $request->post('code');
       $model->MRP = $request->post('MRP');
       $model->image = $image_name;;
       $model->status = $request->post('status');
       $model->save();
       $request->session()->flash('message',$msg);
       return redirect('admin/product');
    }

   public function delete(Request $request,$id)
    {

    $message='';

       $orders=Order::where('product_id','=',$id)->get();
       $c=count($orders);
       if($c>0)
       {
            $message = $c.' Order(s) ';
       }


       $bills =Bill::where('product_id','=',$id)->get();

       $c=count($bills);
       if($c>0)
       {
            $message .=' and '.$c.' Bill(s) ';
       }
         
       if($message =='')
       {
             
       $model = Product::find($id);
       $model->delete();
       return redirect('admin/product')->with('message','Product deleted');
        }
        else 
       {
        return redirect('admin/product')->with('error','Unable to delete as '.$message.' linked with this Product');
       }
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Product::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Product permantly deleted');
       return redirect('admin/product/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Product::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Product Restored');
       return redirect('admin/product/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Product::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Product status changed');
       return redirect('admin/product');
    }
}
