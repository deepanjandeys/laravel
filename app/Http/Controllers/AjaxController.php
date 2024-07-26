<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\SalesAgent;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Product_sub_group;
//use App\Models\VillageAgent;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function getCustomerDetails(Request $request) 
    {
        $name=''; $address=''; $mobile=''; $found='0';
$image='';
     $id=$request['id'];
        if($id !='')
        {
        $arr = Customer::where(['id'=>$id])->get();
        $name       = $arr[0]->name;
        $address    = $arr[0]->address; 
        $mobile     = $arr[0]->mobile;
        $image      = $arr[0]->IdProofImage;
        $found      = '1';
        }
$path = asset('/storage/media')."/";
return response()->json(['name'=> $name,'address'=> $address,'mobile'=> $mobile,'path'=>$path,'image'=>$image,'found'=>$found]);
   }
 
public function getBookingDetails(Request $request) 
    {
        $name=''; $address=''; $mobile=''; $found='0';
        $str='';
     $id=$request['id'];
        if($id !='')
        {
            $orders = array();
            if(session()->get('ADMIN_TYPE')==1)
            {

        $sql="SELECT `id`,`customer_id`,`Sale_agent_id`, `product_id`, `sale_price`, `down_payment`, `EMI`, `EMI_mode`, `EMI_Period`, `LoanAmount`, `IntOnLoan`, `status`, `booking_advance` FROM `orders` WHERE `customer_id`=$id and `deleted_at` is null and `id` not in(SELECT `order_id` FROM `bills` where `order_id` is not null)";
            }
            else if(session()->get('ADMIN_TYPE')==3 || session()->get('ADMIN_TYPE')==4)
            {

            $id=session()->get('ADMIN_ID'); // user id
            $admin=Admin::find($id);
            $sale_agent_id=$admin->refer_id;

        $sql="SELECT `id`,`customer_id`,`Sale_agent_id`, `product_id`, `sale_price`, `down_payment`, `EMI`, `EMI_mode`, `EMI_Period`, `LoanAmount`, `IntOnLoan`, `status`, `booking_advance` FROM `orders` WHERE `customer_id`=$id and `Sale_agent_id`=$sale_agent_id and `deleted_at` is null and `id` not in(SELECT `order_id` FROM `bills` where `order_id` is not null)";
            }
        //$arr = Customer::where(['id'=>$id])->get();
        /////////
        
        $arr = DB::select( DB::raw($sql));

        $i=0;
        if($arr==null) 
        {
         //$stock=0;
        }
        else 
        {
            foreach ($arr as $value) 
            {
            $str .= "<a href='javascript:void(0)' onclick='setOrderDetails(".$i.")'>Order Id ".$value->id."  Product Price ".$value->sale_price.' , Downpayment '.$value->down_payment.' , Booking Advance '.$value->booking_advance.'</a><br>';
            $i++;
            }
        }
        ////////
        $found = $i;
        }

return response()->json(['str'=> $str,'found'=>$found,'arr'=>$arr]);
   }
 
  public function getBranchDetails(Request $request) 
    {
        $name=''; $address=''; $mobile=''; $found='0';

     $id=$request['id'];
        if($id !='')
        {
        $arr = Branch::where(['id'=>$id])->get();
        $name       = $arr[0]->name;
        $address    = $arr[0]->address; 
        $mobile     = $arr[0]->mobile;
        $found      = '1';
        }

return response()->json(['name'=> $name,'address'=> $address,'mobile'=> $mobile,'found'=>$found]);
   }
   public function getSaleAgentDetails(Request $request) 
    {
       $name=''; $address=''; $mobile=''; $found='0';
        
        $id=$request['id'];
        if($id !='')
        {
        $arr = SalesAgent::where(['id'=>$id])->get();
        $name       = $arr[0]->name;
        $address    = $arr[0]->address; 
        $mobile     = $arr[0]->mobile;
        
        $found='1';
        }
      return response()->json(['name'=> $name,'address'=> $address,'mobile'=> $mobile,'found'=>$found]);
   }
public function getProductDetails(Request $request) 
    {
       $name=''; $code=''; $MRP=''; $image=''; $GroupId='0'; 
       $product_group_name=''; $found='0'; $SubGroupId=0;$product_sub_group_name ='';$path =''; $description='';
        $loan_type=0;
        $id=$request['id'];
        if($id !='')
        {
        
        $arr = Product::where(['id'=>$id])->get();
        
        $name       = $arr[0]->name;
        $code       = $arr[0]->code; 
        $MRP        = $arr[0]->MRP;
        $image      = $arr[0]->image;
        $GroupId   = $arr[0]->GroupId;
        $SubGroupId   = $arr[0]->SubGroupId;
        $description = $arr[0]->description;
        $path = asset('/storage/media')."/";
       
        $arr1 = ProductGroup::where(['id'=>$GroupId])->get();
        $product_group_name = $arr1[0]->name;
        $loan_type = $arr1[0]->loan_type;

        $query = DB::select( DB::raw("SELECT `name`  FROM `loan_types` WHERE `id`='".$arr1[0]->loan_type."'"));

        if($query!=null)  
        {
            $loan_type_name=$query[0]->name;
        }
        else 
        {
            $loan_type_name='';
        }


        if($SubGroupId>0)
        {
            $arr2 = Product_sub_group::where(['id'=>$SubGroupId])->get();
            $product_sub_group_name = $arr2[0]->name;
        }
        else 
        {
            $product_sub_group_name='';
        }
        $found='1'; 
        }
      return response()->json(['name'=> $name,'code'=> $code,'MRP'=> $MRP,'image' => $image,'GroupId'=>$GroupId,'SubGroupId'=>$SubGroupId,'product_group_name' => $product_group_name,'product_sub_group_name' => $product_sub_group_name,'loan_type'=>$loan_type,'loan_type_name'=>$loan_type_name,'found'=>$found,'path'=>$path,'description'=>$description]);
   }

public function getStockDetails(Request $request) 
    {
        $stock=''; $found='0';
        $branch_id=$request['branch_id']; 
        $product_id=$request['product_id']; 

$sql="SELECT `stock` FROM `branch_stocks` WHERE `branch_id` = ".$branch_id." AND `product_id` =".$product_id;
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
         $stock=0;
        }
        else 
        {
        $stock=$query[0]->stock;
         $found='1';
        }
      return response()->json(['stock' => $stock,'found'=>$found]);
   }


public function getBillLastEMI(Request $request) 
    {
        $found='0'; $id=0;
        $bill_id=$request['id']; 
$sql="SELECT id,`final_submit` FROM `bills` WHERE `id`=$bill_id";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $found=5;
            goto lastLine;
        }
        else 
        {
            $final_submit=$query[0]->final_submit;
            if($final_submit==0)
                $found=2;   // error , not approved
            else 
                $found=1;
        }

        $ADMIN_TYPE=session()->get('ADMIN_TYPE');
        $ss='';
        if($ADMIN_TYPE==4)
        {
            // if vilage
            $id=session()->get('ADMIN_ID'); // user id
            $admin=Admin::find($id);
            $village_agent_id=$admin->refer_id;

$ss="SELECT b.`id` FROM `bills` as b JOIN  `customers` as c ON  b.`customer_id`=c.`id` JOIN  `villages` as v ON v.`id`=c.`village_id` JOIN `village_agents`  as va ON v.`id`=va.`village_id`
WHERE va.`id`=".$village_agent_id." and b.`id`=".$bill_id." and b.`deleted_at` is NULL;";

        $query = DB::select( DB::raw($ss));
        if(count($query)==0) 
        {
         //$stock=0;
            $found='4';
            goto lastLine;
        }
        
        }
if($found=='1')
{
$sql="SELECT min(`id`) as id FROM `emi_collections` WHERE `bill_id`=$bill_id and `collect_time` is null and `deleted_at` is NULL;";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $id=0;
        }
        else 
        {
            $id=$query[0]->id;
            if($id==null)
            {
                $id=0;
                $found=3;   // all emi paid
            }
            else 
                $found=1;
        }

}

if($found==3)
{
$sql="SELECT max(`id`) as id FROM `emi_collections` WHERE `bill_id`=$bill_id and `collect_time` is not null and `deleted_at` is NULL;";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $id=0;
        }
        else 
        {
            $id=$query[0]->id;
        }
}
lastLine:
        $typeName=session()->get('typeName');
      return response()->json(['id' => $id,'typeName'=>$typeName,'found'=>$found]);
}

public function calculate_emi_clear(Request $request) 
    {
        $found='0'; $c=0; $EMI_Loan=0;
        $bill_id=$request['id']; $clearValue=0;

$sql="SELECT count(`id`) as c FROM `emi_collections` WHERE `bill_id`=$bill_id and `collect_time` is null and `deleted_at` is NULL;";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $c=0;
            $found='0';
        }
        else 
        {
            $c=$query[0]->c;
            $found='1';
        }

$sql="SELECT `EMI_Loan` FROM `bills` WHERE `id`=$bill_id ";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $EMI_Loan=0;
            $found='0';
        }
        else 
        {
            $EMI_Loan=$query[0]->EMI_Loan;
            $found='1';
        }
        
        $clearValue=$c*$EMI_Loan;
        $str= $c.' X '.$EMI_Loan;

      return response()->json(['clearValue' => $clearValue,'str'=>$str,'found'=>$found]);
   }

public function clearAllEMI(Request $request) 
    {
        $found='0'; $c=0; $EMI_Loan=0;
        $bill_id=$request['id']; $clearValue=0;
$id=session()->get('ADMIN_ID'); // user id

$sql="SELECT `EMI_Loan` FROM `bills` WHERE `id`=$bill_id ";
        $query = DB::select( DB::raw($sql));
        if(count($query)==0) 
        {
            $EMI_Loan=0;
            $found='0';
        }
        else 
        {
            $EMI_Loan=$query[0]->EMI_Loan;
            $found='1';
        }

        DB::table('emi_collections')
        ->where('bill_id', $bill_id)  
        ->whereNull('collect_time')
        ->whereNull('deleted_at')
        ->update(array('paid_amt' => $EMI_Loan,'collect_by'=>$id,'collect_time'=>date('Y-m-d H:i:s'),'EMI_interest'=>0));  // update the record in the DB. 

      return response()->json(['found'=>$found]);
   }

public function getCheckMobileUnique(Request $request) 
    {
        $found='0'; $message='';
        $mobile=$request['mobile']; 

        $query = DB::select( DB::raw("SELECT `type` FROM `admins` WHERE `name` ='".$mobile."'"));

        if($query!=null)  
        {
         $type=$query[0]->type;
         $found='1';

         if($type=='1')
            $message='Admin created with this mobile number';
         else if($type=='3')
            $message='already Sale agent account created with this '.$mobile.' number';
          else if($type=='4')
            $message='already Village agent account created with this '.$mobile.' number';
        }
      return response()->json(['message' => $message,'found'=>$found]);
   }

public function setDefaultPasswordSA(Request $request) 
    {
     $id=$request['id'];
     $newPassword='123';
        if($id !='')
        {
        $model = Admin::where('type','=','3')->where('refer_id','=',$id)->first(); 
        $found      = '1';
        $model->password = Hash::make($newPassword);
        $msg = 'Sale Agent password reset';
        $model->save();
        }

return response()->json(['msg'=> $msg,'found'=>$found]);
   }

public function setDefaultPasswordVA(Request $request) 
    {
     $id=$request['id'];
     $newPassword='123';
        if($id !='')
        {
        $model = Admin::where('type','=','4')->where('refer_id','=',$id)->first(); 
        $found      = '1';
        $model->password = Hash::make($newPassword);
        $msg = 'Village Agent password reset';
        $model->save();
        }

return response()->json(['msg'=> $msg,'found'=>$found]);
   }
public function csrf()
{
     return csrf_token(); 
}
}
