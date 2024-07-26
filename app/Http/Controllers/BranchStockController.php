<?php

namespace App\Http\Controllers;

use App\Models\BranchStock;
use App\Models\BranchStockDetails;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchStockController extends Controller
{
    public function index(Request $request)
    {
        $search=$request['search'] ?? "";
       
        if($search !="")
        {
$BranchStock = BranchStock::with('getBranch')->with('getProduct')->where(function ($query) use ($search){
                $query->where('branch_id', '=', $search)->orwhere('product_id','=',$search)->orwhere('stock','=',$search);
            })
            ->latest()->simplepaginate(10);
        }
        else
        {   
$BranchStock  = BranchStock::with('getBranch')->with('getProduct')->latest()->simplepaginate(10);
        }
      
        $result = compact('BranchStock','search');
        return view('admin.branchStock',$result); 
    }
    
    
    /*public function trash()
    {
        $result['data'] =   BranchStock::onlyTrashed()->get();
        return view('admin.branch-trash',$result); 
    }
    */
    public function purchase(Request $request)
    {
    
        $result['id']='0';
        $result['branch_id']='';
        $result['product_id']= '';
        $result['qty']= '';
        $result['purchase_date']= date('d-m-Y');
                    
        $result['Branches'] = Branch::all(); 
        $result['products'] = Product::all(); 
       return view('admin.purchase',$result); 
    }
public function stockTransfer(Request $request)
    {
       
        $result['id']='0';
        $result['branch_id']='';
        $result['product_id']= '';
        $result['qty']= '';
                    
        $result['Branches'] = Branch::all(); 
        $result['products'] = Product::all(); 
       return view('admin.stockTransfer',$result); 
    }
    public function manage_branchStock_process(Request $request)
    {
        
       $request->validate([
        'branch_id'=>'required',
        'product_id'=>'required',
        'qty'=>'required',
       ]
       ); 
$query = DB::select( DB::raw("SELECT * FROM `branch_stocks` 
    WHERE `branch_id` = ".$request->post('branch_id')." AND `product_id` =".$request->post('product_id')));
   $id=0;

       if ($query==null) 
        {
            $branchStock = new BranchStock();
            $branchStock->branch_id= $request->post('branch_id');
            $branchStock->product_id= $request->post('product_id');
            $msg = 'Branch Stock Inserted';
       }
       else 
       {
            $id=$query[0]->id;
            $branchStock = BranchStock::find($id);
            $msg = 'Branch Stock Updated';
       }
        
       $stock=(int)$branchStock->stock;
       $qty=(int)$request->post('qty');
       $stock += $qty ;
       $branchStock->stock = $stock;
       
       $branchStock->save();

       ////////////////////////
            $branchStockDetails = new BranchStockDetails();
            $branchStockDetails->branch_id= $request->post('branch_id');
            $branchStockDetails->product_id= $request->post('product_id');
            $branchStockDetails->txn_date= $request->post('purchase_date');
            $branchStockDetails->qty= $request->post('qty');
            $branchStockDetails->current_stock =$stock;
            $branchStockDetails->mode = 1; // purchase 
            $branchStockDetails->refer_id=0; // not applicable
            $branchStockDetails->save();
        ////////////////////////////////////////
       $request->session()->flash('message',$msg);
       return redirect('admin/branchStock');
       
    }

public function manage_branchStock_transfer(Request $request)
    {
        
       $request->validate([
        'frm_branch_id'=>'required',
        'to_branch_id'=>'required',
        'product_id'=>'required',
        'qty'=>'required',
       ]
       ); 
$qty=(int)$request->post('qty');

$query = DB::select( DB::raw("SELECT * FROM `branch_stocks` 
    WHERE `branch_id` = ".$request->post('frm_branch_id')." AND `product_id` =".$request->post('product_id')));
   $frm_id=0; $frm_stock=0;
   if($query !=null)
   {
    $frm_id     =$query[0]->id;
    $frm_stock  =(int)$query[0]->stock;
    $frm_stock -= $qty;
    $frm_branchStock = BranchStock::find($frm_id);
    $frm_branchStock->stock = $frm_stock;
    $frm_branchStock->save();
   }

$query = DB::select( DB::raw("SELECT * FROM `branch_stocks` 
    WHERE `branch_id` = ".$request->post('to_branch_id')." AND `product_id` =".$request->post('product_id')));
   $to_id=0;  $to_stock=0; 

       if ($query==null) 
        {
            $to_branchStock = new BranchStock();
            $to_branchStock->branch_id= $request->post('to_branch_id');
            $to_branchStock->product_id= $request->post('product_id');
            $msg = 'Branch Stock Inserted';
       }
       else 
       {
            $to_id=$query[0]->id;
            $to_branchStock = BranchStock::find($to_id);
            $msg = 'Branch Stock Updated';
       }
        
       $to_stock=(int)$to_branchStock->stock;
       
       $to_stock += $qty ;
       $to_branchStock->stock = $to_stock;
       
       $to_branchStock->save();

       ////////////////////////
            $branchStockDetails = new BranchStockDetails();
            $branchStockDetails->branch_id= $request->post('frm_branch_id');
            $branchStockDetails->product_id= $request->post('product_id');
            $branchStockDetails->qty= $request->post('qty');
            $branchStockDetails->current_stock =$frm_stock;
            $branchStockDetails->mode = 3; // transfer to 
            $branchStockDetails->refer_id= $request->post('to_branch_id');; // branch id
            $branchStockDetails->save();
        ////////////////////////////////////////
            ////////////////////////
            $branchStockDetails = new BranchStockDetails();
            $branchStockDetails->branch_id= $request->post('to_branch_id');
            $branchStockDetails->product_id= $request->post('product_id');
            $branchStockDetails->qty= $request->post('qty');
            $branchStockDetails->current_stock =$to_stock;
            $branchStockDetails->mode = 4; // recived from 
            $branchStockDetails->refer_id= $request->post('frm_branch_id');; // branch
            $branchStockDetails->save();
        ////////////////////////////////////////
       $request->session()->flash('message',$msg);
       return redirect('admin/branchStock');
       
    }
/*
   public function delete(Request $request,$id)
    {
       
       $model = BranchStock::find($id);
       $model->delete();
       $request->session()->flash('message','BranchStock deleted');
       return redirect('admin/branch');
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = BranchStock::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','BranchStock permantly deleted');
       return redirect('admin/branch/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = BranchStock::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','BranchStock Restored');
       return redirect('admin/branch/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = BranchStock::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','BranchStock status changed');
       return redirect('admin/BranchStock');
    }
*/
}
