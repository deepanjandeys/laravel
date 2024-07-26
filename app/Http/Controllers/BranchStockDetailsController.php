<?php

namespace App\Http\Controllers;

use App\Models\BranchStockDetails;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchStockDetailsController extends Controller
{
    public function index(Request $request)
    {
        $operator=$request['operator'] ?? "=";
        $quantity=$request['quantity'] ?? "";
        $Date_From=$request['Date_From'] ?? "";
        $Date_To=$request['Date_To'] ?? "";
        $product_ids=$request['product_ids'] ?? array();
        /////
     
     try
     {
        if($product_ids[0]=='')
            $product_ids=array();
     }
     catch(\Exception $e)
     {
        
     }
        
        
//////////////////////////////////////////////////////
if($quantity !='' || isset($product_ids[0]))
        {

//echo 'search status '.$status;
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

        
////////////        
 if($Date_From !='')
    $Date_from =date('Y-m-d',strtotime($Date_From));
 else 
    $Date_from='';

 if($Date_To !='')
    $Date_to =date('Y-m-d',strtotime($Date_To));
 else 
    $Date_to ='';

$branchStockDetails = BranchStockDetails::with('getBranch')->with('getProduct')->when(!empty($data['quantity'])  , function ($query) use($operator,$data){
return $query->where('qty', $operator, $data['quantity']);
})
->when($Date_from !='' && $Date_to !='' , function ($query) use($Date_from,$Date_to){
return $query->whereBetween('order_date', [$Date_from, $Date_to]);
})
->when (!empty($product_ids) , function ($query) use($product_ids)
{
return $query->whereIn('product_id', $product_ids);
})
->latest()->simplepaginate(10);
//print_r(DB::getQueryLog());
        $displaySearch='';
        $a_search_text='Hide Search';
        }
        else
        {
    $Products=Product::all();
    $branchStockDetails  = BranchStockDetails::with('getBranch')->with('getProduct')->latest()->simplepaginate(10);
   
        $displaySearch='display:none;';
        $a_search_text='Show Search';
    }
    $operators=DB::table('operators')->get();

    $result = compact('quantity','branchStockDetails','Products','product_ids','displaySearch','a_search_text','Date_From','Date_To','operator','operators');
        return view('admin.branchStockDetails',$result); 
    }
    
}
