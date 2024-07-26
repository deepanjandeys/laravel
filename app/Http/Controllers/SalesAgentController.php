<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Bill;
use App\Models\SalesAgent;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesAgentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->session()->has('ADMIN_LOGIN')) 
        {            
            return redirect('sale_agent/dashboard');
        }
        else
        {
           $result['type']=3;
           return view('admin.login',$result);
        }

    }
     public function dashboard()
    {
        $sql="SELECT sum(`paid_amt`) as `collected_amount` FROM `emi_collections` WHERE `collect_by`=".session()->get('ADMIN_ID')." and `recieve_by`=0 and `deleted_at` is null";
//echo $sql;

        $query = DB::select( DB::raw($sql));
            if($query !=null)
            {
            $result['collected_amount']=$query[0]->collected_amount;
            }
            else 
            {
            $result['collected_amount']=0;
            }
$sql="SELECT sum(`paid_amt`) as `recieved_amount` FROM `emi_collections` WHERE `recieve_by`=".session()->get('ADMIN_ID')." and `deleted_at` is null";
//echo '<br>'.$sql;
        $query = DB::select( DB::raw($sql));
            if($query !=null)
            {
            $result['recieved_amount']=$query[0]->recieved_amount;
            }
            else 
            {
            $result['recieved_amount']=0;
            }


        return view('admin.dashboard_sa',$result);
    }

    public function list(Request $request)
    {
        $search=$request['search'] ?? "";
        if($search !="")
        {
$salesAgent = SalesAgent::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%')->orwhere('address','like',"%$search%");
            })
            ->latest()->simplepaginate(10);

        }
        else
        {   
     $salesAgent  = SalesAgent::latest()->simplepaginate(10);
        }
        $Branches = DB::table('branches')->get(); 
        $result = compact('salesAgent','search','Branches');
        return view('admin.salesAgent',$result); 
    }    
     public function trash()
    {
        $result['data'] =   SalesAgent::onlyTrashed()->get();
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.salesAgent-trash',$result); 
    }

    public function edit_sale_agent(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = SalesAgent::where(['id'=>$id])->get();
            $admin = Admin::where(['refer_id'=>$id])->get();

            $result['id']= $arr[0]->id;
            $result['admin_id']= $admin[0]->id;
            $result['name']= $arr[0]->name;
            $result['mobile']= $arr[0]->mobile;
            $result['address']= $arr[0]->address;
            $result['pin']= $arr[0]->pin;
            $result['email']= $arr[0]->Admins->email;
            $result['status']= $arr[0]->Admins->status;
            $result['isApproved']= $arr[0]->Admins->isApproved;
            $result['password']= '';
        }
        else
        {
            $result['id']= '0';
            $result['admin_id']= '0';
            $result['name']= '';
            $result['mobile']= '';
            $result['email']= '';
            $result['password']= '';
            $result['address']= '';
            $result['pin']= '';
            $result['isApproved']=1;
            $result['status']= '1';
        }
        //print_r($result);
        $result['statuses'] = DB::table('status')->get(); 
       return view('admin.edit_SalesAgent',$result); 
    }

    public function manage_sale_agent_process(Request $request)
    {
        
       $request->validate([
        'name'=>'required',
        'mobile'=>'required|unique:admins,name,'.$request->post('admin_id'),
        'address'=>'required',
        'pin'=>'required',
       ]
       ); 

       if ($request->post('id')>0) 
       {
           $model = SalesAgent::find($request->post('id'));
           $msg = 'SalesAgent updated';
       }
       else
       {
            $model = new SalesAgent();
            $msg = 'SalesAgent Inserted';
       }
       
       $model->name = $request->post('name');
       $model->mobile = $request->post('mobile');
       $model->address = $request->post('address');
       $model->pin = $request->post('pin');
       $model->status = $request->post('status');
       $model->save();

       ////////         /////
       if ($request->post('id')==0)  
       { // again check if sale agent new then add new row at admin
            $password=$request->post('password');
            $admin=new Admin();
            $admin->name=$request->post('mobile');
            //$admin->mobile=$request->post('mobile');
            $admin->email=$request->post('email');
            $admin->password=Hash::make($password);
            $admin->type=3;
            $admin->refer_id=$model->id;
            $admin->image='';
            $admin->isApproved=1;
            $admin->status=1;
            $admin->save();
       }
         
       //////
       $request->session()->flash('message',$msg);
       return redirect('admin/sale_agent');
    }

   public function delete(Request $request,$id)
    {
       $message='';

       $orders=Order::where('sale_agent_id','=',$id)->get();
       $c=count($orders);
       if($c>0)
       {
            $message = $c.' Order(s) ';
       }


       $bills =Bill::where('sale_agent_id','=',$id)->get();

       $c=count($bills);
       if($c>0)
       {
            $message .=' and '.$c.' Bill(s) ';
       }
       
       $typeName=session()->get('typeName');
       
       if($message =='')
       {
        $model = SalesAgent::find($id);
        $model->delete();
        return redirect($typeName.'/sale_agent')->with('message','SalesAgent deleted'); 
       }
       else 
       {
        return redirect($typeName.'/sale_agent')->with('error','Unable to delete as '.$message.' linked with this SalesAgent');
       }
       
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = SalesAgent::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Sales Agent permantly deleted');
       return redirect('admin/sale_agent/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = SalesAgent::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Sales Agent Restored');
       return redirect('admin/sale_agent/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = SalesAgent::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Sales Agent status changed');
       return redirect('admin/sale_agent');
    }


    public function forgetPassword(Request $request)
    {
        $mobile = ltrim($request->post('mobile'), "0");
        $id=0;

        $result = SalesAgent::where(['mobile'=>$mobile])->first();
        if (isset($result))
        {
           $admin=Admin::where('refer_id','=',$result->id)->get();
           $email =  $admin[0]->email; 
           $id    =  $result->id;
           if($email=='')
            {
            $request->session()->flash('error','Your email id is not set,we unable to send password reset link to your email, ask Admin to create a password reset link to you');
            return redirect('sale_agent/forget_password');   
            }

           $subject = urlencode("Forget password");
           $fun = new FunctionsController();
           $enId = $fun->encrypt($id);
           $t_url= $fun->get_tiny_url("http://127.0.0.1:8000/sale_agent/ResetPassword/".$enId);


$bodyText = urlencode("<a href='".$t_url."'>Click here to reset password </a>");
$url="https://safego.co.in/gmailApi/index.php?email=$email&subject=$subject&body=$bodyText&formHeader=XCL";
//echo '<a href="'.$url.'" target="_blank">mail link</a>';
//die();
$ch = curl_init($url);
$timeout=20;
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_HEADER, 0);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);    
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);  
$fetchdata = curl_exec($ch);        
curl_close($ch);
       return redirect('sale_agent/forget_password_success/'.$id);
     
        }
        else
        {
            $request->session()->flash('error','Mobile number not exist');
            return redirect('sale_agent/forget_password');
        }
        
    }
    
    public function forget_password_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = SalesAgent::where(['id'=>$id])->get();
            $admin=Admin::where('refer_id','=',$arr[0]->id)->get();

            if($arr->count() >0)
            {
            $fun = new FunctionsController();
            $email = $fun->maskEmail($admin[0]->email);
            $result['email']= $email;
            }
        }
        else
        {
            $result['email']= '';
        }
       return view('admin.sale_agent_forget_password_success',$result); 
    }

public function password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            
        }
     
        
       return view('admin.admin_password_change_success',$result); 
    }
    public function manage_password_reset(Request $request)
    {
       $request->validate([
        'newPassword'=>'required'
       ]
       ); 
       $id=$request->post('id');
       
       if ($id>0) 
       {
            //$password = $request->post('password');
            $newPassword = $request->post('newPassword');
            $model = Admin::where('refer_id',$id)->where('type','=',3)->first(); 

        if (isset($model))
        {
            
            //if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('sale_agent/password_change_success/'.$id);
            }
           /* else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('sale_agent/password_change_success/'.$id)->with('error',$msg);

            }
            */
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('sale_agent/changePassword/'.$id);
        }
       }
       
        
    }

public function sale_agent_password_reset(Request $request,$id='')
    {
            
        if ($id!='') 
        {
           
           $fun = new FunctionsController();
            $id = (int)$fun->decrypt($id);
            

            $arr = SalesAgent::where(['id'=>$id])->get();
            //echo "Count ".$arr->count();
            if($arr->count()==0)
            {
                return redirect('no-access');       
            }
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
        }
     
        
       return view('admin.sale_agent_reset_password',$result); 
    }
    public function sale_agent_password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = SalesAgent::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            
        }
       return view('admin.sale_agent_password_change_success',$result); 
    }
    public function manage_sale_agent_password_reset(Request $request)
    {
        $s=" 'password'=>'required',";

       $request->validate([
        'newPassword'=>'required'
       ]
       ); 
       $id=$request->post('id');
       if ($id>0) 
       {
            //$password = $request->post('password');
            $newPassword = $request->post('newPassword');
            $model = SalesAgent::find($id); 

        if (isset($model))
        {
            $model->password = Hash::make($newPassword);
            $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('sale_agent/password_change_success/'.$id);
            
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('sale_agent/ResetPassword/'.$id);
        }
       }
       
        
    }
   public function sale_agent_password_change(Request $request)
    {
       $request->validate([
        'password'=>'required',
        'newPassword'=>'required'
       ]
       ); 
       $id=$request->post('id');
       if ($id>0) 
       {
            $password = $request->post('password');
            $newPassword = $request->post('newPassword');
            $model = SalesAgent::find($id); 

        if (isset($model))
        {
            if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('sale_agent/password_change_success/'.$id);
            }
            else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('sale_agent/password_change/'.$id)->with('error',$msg);

            }
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            //return redirect('sale_agent/changePassword/'.$id);
        }
       }   
    }


public function reset(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::find($id);
            $result['id']= $arr->refer_id;
            $result['name']= $arr->name;
            
        }
     
        
       return view('admin.sale_agent_reset_password',$result); 
    }
}
