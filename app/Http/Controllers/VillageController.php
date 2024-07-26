<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\VillageAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class VillageController extends Controller
{
     public function index(Request $request)
    {
        if ($request->session()->has('ADMIN_LOGIN')) 
        {
            //echo '<br>('.$request->session()->get('VILLAGE_AGENT_LOGIN').')';
            return redirect('village_agent/dashboard');
        }
        else
        {
           $result['type']=4;
           return view('admin.login',$result);
        }

    }
    public function dashboard()
    {
        $sql="SELECT sum(`paid_amt`) as `collected_amount` FROM `emi_collections` WHERE `collect_by`=".session()->get('ADMIN_ID')." and `recieve_by` =0 and `deleted_at` is null";

        $query = DB::select( DB::raw($sql));
            if($query !=null)
            {
            $result['collected_amount']=$query[0]->collected_amount;
            }
            else 
            {
            $result['collected_amount']=0;
            }

        return view('admin.dashboard_va',$result);
    }
    
   public function list(Request $request)
    {
        $search=$request['search'] ?? "";
        $rows=$request['rows']??'15';

        if($search !="")
        {
$village = Village::where(function ($query) use ($search){
                $query->where('name', 'like', '%'.$search.'%')->orwhere('district','like',"%$search%");
            })
            ->latest()->simplepaginate($rows);
        }
        else
        {    
             $village  = Village::with('VillageAgents')->latest()->simplepaginate($rows);
        }
       
    $id_proof_types = DB::table('id_proof_types')->get(); 
    $address_proof_types = DB::table('address_proof_types')->get();
    $sales_agents = DB::table('sales_agents')->get(); 
        $result = compact('village','search','id_proof_types','address_proof_types','sales_agents','rows');
        return view('admin.village',$result); 
    }
    
    public function trash()
    {
        $result['data'] =   Village::onlyTrashed()->get();
        $result['statuses'] = DB::table('status')->get(); 
        return view('admin.village-trash',$result); 
    }

    public function edit_village(Request $request,$id='')
    {
        $search='';
        //DB::enableQueryLog();

        // and then you can get query log
        if ($id>0) 
        {
            $arr = Village::with('VillageAgents')->find($id);
    $admin = Admin::where(['refer_id'=>$arr->VillageAgents->id])->get();
            
            $result['id']= $arr->id;
            $result['admin_id']= $admin[0]->id;
            $result['name']= $arr->name;
            $result['pin']= $arr->pin;
            $result['agent_id']= $arr->VillageAgents->id;
            $result['agent_name']= $arr->VillageAgents->name;
            $result['address']= $arr->VillageAgents->address;
            $result['mobile']= $arr->VillageAgents->mobile;
            $result['email']= $arr->VillageAgents->Admins->email;
            $result['password']='';
            $result['IdProofType']= $arr->VillageAgents->IdProofType;
            $result['IdProofImage']= $arr->VillageAgents->IdProofImage;
            $result['IDProofImagePath']= $arr->VillageAgents->IDProofImagePath();
        $result['AddressProofType']= $arr->VillageAgents->AddressProofType;
        $result['AddressProofImage']= $arr->VillageAgents->AddressProofImage;
        $result['AddressProofImagePath']= $arr->VillageAgents->AddressProofImagePath();
            $result['district']= $arr->district;
            $result['sub_division']= $arr->sub_division;
            $result['status']= $arr->status;
        }
        else
        {
            $result['id']='0';
            $result['admin_id']= '0';
            $result['name']= '';
            $result['pin']= '';
            $result['address']= '';
            $result['mobile']= '';
            $result['email']= '';
            $result['password']='';
            $result['agent_id']= 0;
            $result['agent_name']= '';
            $result['IdProofType']= '';
            $result['IdProofImage']= ''; 
        $result['IDProofImagePath']='/storage/media/NoImage.png';
            $result['AddressProofType']= '';
            $result['AddressProofImage']= '';
        $result['AddressProofImagePath']='/storage/media/NoImage.png';
            $result['district']= '';
            $result['sub_division']= '';
            $result['status']= '1';
            
        }
        $result['statuses'] = DB::table('status')->get(); 
        $result['id_proof_types'] = DB::table('id_proof_types')->get(); 
        $result['address_proof_types'] = DB::table('address_proof_types')->get(); 
       return view('admin.edit_Village',$result); 
    }

    public function manage_village_process(Request $request)
    {
        $village_id=0;

       if ($request->post('id')>0) 
       {
            $image_validation="mimes:jpeg,jpeg,png,gif";
       }
       else
       {
        $image_validation="required|mimes:jpeg,jpeg,png,gif";    
       } 
       $request->validate([
        'name'=>'required',
        'pin'=>'required',
        'district'=>'required',
        'sub_division'=>'required',
        'mobile'=>'required|unique:admins,name,'.$request->post('admin_id'),
        'agent_name'=>'required',
        'address'=>'required',
        'IdProofType'=>'required',
        'IdProofImage'=>$image_validation,
        'AddressProofType'=>'required',
        'AddressProofImage' => $image_validation,
       ]
       ); 
 

       if ($request->post('id')>0) 
       {
           $model = Village::find($request->post('id'));
           $msg = 'Village updated';
       }
       else
       {
            $model = new Village();
            $msg = 'Village Inserted';
       }
       
       $model->name = $request->post('name');
       $model->pin = $request->post('pin');
       $model->district = $request->post('district');
       $model->sub_division = $request->post('sub_division');
       $model->status = $request->post('status');
       $model->save();
       $village_id=$model->id;
       $request->session()->flash('message',$msg);
       ////////////////////


        $image_validation='';
        $IdProofImage_name='';
        $AddressProofImage_name='';

       
       if ($request->post('agent_id')>0) 
       {
    $model = VillageAgent::find($request->post('agent_id'));
    $msg = 'village agent updated';
       }
       else
       {
            $model = new VillageAgent();
            $msg = 'village agent  Inserted';
       }
       
        if($request->hasfile('IdProofImage'))
       {
            $ext=$request->file('IdProofImage')->getClientOriginalExtension();
            //echo "ID proof".$ext;
            $IdProofImage_name = 'vi_'.$request->post('agent_name').'_idProof_'.time().'.'.$ext;
            $request->file('IdProofImage')->storeAs('/media/',$IdProofImage_name);
            
        }
        else 
        {
            $IdProofImage_name=$request->post('hdIdProofImage');
        }
        

       if($request->hasfile('AddressProofImage'))
       {
            $ext=$request->file('AddressProofImage')->getClientOriginalExtension();
            //echo "<br>Address ".$ext;
            $AddressProofImage_name = 'vi_'.$request->post('agent_name').'_adProof_'.time().'.'.$ext;
            $request->file('AddressProofImage')->storeAs('/media/',$AddressProofImage_name);
        }
        else 
        {
    $AddressProofImage_name=$request->post('hdAddressProofImage');
        }
       $model->name = $request->post('agent_name');
       $model->village_id = $village_id;
       $model->mobile = $request->post('mobile');
       $model->address = $request->post('address');
       $model->pin = $request->post('pin');
       $model->IdProofType = $request->post('IdProofType');
       $model->IdProofImage = $IdProofImage_name;
       $model->AddressProofType = $request->post('AddressProofType');
       $model->AddressProofImage = $AddressProofImage_name;
       $model->isApproved = 1;
       $model->status = $request->post('status');
       $model->save();
       ////
       if ($request->post('agent_id')==0)  
       { // again check if sale agent new then add new row at admin
            $password=$request->post('password');
            $admin=new Admin();
            $admin->name=$request->post('mobile');
            //$admin->mobile=$request->post('mobile');
            $admin->email=$request->post('email');
            $admin->password=Hash::make($password);
            $admin->type=4;
            $admin->refer_id=$model->id;
            $admin->image='';
            $admin->isApproved=1;
            $admin->status=1;
            $admin->save();
       }
       $request->session()->flash('message',$msg);
       $typeName=session()->get('typeName');
       return redirect($typeName.'/village');
    }

   public function delete(Request $request,$id)
    {
     
     $message='';

       $customers=Customer::where('village_id','=',$id)->get();
       $c=count($customers);
       if($c>0)
       {
            $message = $c.' Customer(s) ';
       }
       
       $typeName=session()->get('typeName');

       if($message =='')
       {
        $model = Village::find($id);
        $model->delete();
        return redirect($typeName.'/village')->with('message','Village deleted'); 
       }
       else 
       {
        return redirect($typeName.'/village')->with('error','Unable to delete as '.$message.' linked with this Village');
       }
    }

    public function forceDelete(Request $request,$id)
    {
       
       $model = Village::withTrashed()->find($id);
       $model->forceDelete();
       $request->session()->flash('message','Village permantly deleted');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/village/trash');
    }

    public function restore(Request $request,$id)
    {
       
       $model = Village::withTrashed()->find($id);
       $model->restore();
       $request->session()->flash('message','Village Restored');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/village/trash');
    }

    public function status(Request $request,$status,$id)
    {
       
       $model = Village::find($id);
       $model->status = $status;
       $model->save();
       $request->session()->flash('message','Village status changed');
       $typeName=session()->get('typeName');
       return redirect($typeName.'/village');
    }

public function forgetPassword(Request $request)
    {
        $mobile = ltrim($request->post('mobile'), "0");
        $id=0;

        $result = VillageAgent::where(['mobile'=>$mobile])->first();
        if (isset($result))
        {
           $admin=Admin::where('refer_id','=',$result->id)->where('type','=',4)->get();
           $email =  $admin[0]->email; 
           $id    =  $result->id;
           if($email=='')
            {
            $request->session()->flash('error','Your email id is not set,we unable to send password reset link to your email, ask Admin to create a password reset link to you');
            return redirect('village_agent/forget_password');   
            }

           $subject = urlencode("Forget password");
           $fun = new FunctionsController();
           $enId = $fun->encrypt($id);
           $t_url= $fun->get_tiny_url("http://127.0.0.1:8000/village_agent/ResetPassword/".$enId);


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
       return redirect('village_agent/forget_password_success/'.$id);
     
        }
        else
        {
            $request->session()->flash('error','Mobile number not exist');
            return redirect('village_agent/forget_password');
        }
        
    }
    
    public function forget_password_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = VillageAgent::where(['id'=>$id])->get();
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
       return view('admin.village_agent_forget_password_success',$result); 
    }

public function password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = VillageAgent::where(['id'=>$id])->get();
            $arr=Admin::where('refer_id','=',$arr[0]->id)->get();

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
            $model = Admin::where('refer_id',$id)->where('type','=',4)->first(); 
        if (isset($model))
        {
            
            //if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('village_agent/password_change_success/'.$id);
            }
           /* else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('village_agent/password_change_success/'.$id)->with('error',$msg);

            }
            */
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('village_agent/changePassword/'.$id);
        }
       }
       
        
    }

public function village_agent_password_reset(Request $request,$id='')
    {
            
        if ($id!='') 
        {
           
           $fun = new FunctionsController();
            $id = (int)$fun->decrypt($id);
            

            $arr = VillageAgent::where(['id'=>$id])->get();
            //echo "Count ".$arr->count();
            if($arr->count()==0)
            {
                return redirect('no-access');       
            }
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
        }
     
        
       return view('admin.village_agent_reset_password',$result); 
    }
    public function village_agent_password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = VillageAgent::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            
        }
       return view('admin.village_agent_password_change_success',$result); 
    }
    public function manage_village_agent_password_reset(Request $request)
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
            $model = VillageAgent::find($id); 

        if (isset($model))
        {
            $model->password = Hash::make($newPassword);
            $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
    return redirect('village_agent/password_change_success/'.$id);
            
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('village_agent/ResetPassword/'.$id);
        }
       }
       
        
    }
   public function village_agent_password_change(Request $request)
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
            $model = VillageAgent::find($id); 

        if (isset($model))
        {
            if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('village_agent/password_change_success/'.$id);
            }
            else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('village_agent/password_change/'.$id)->with('error',$msg);

            }
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            //return redirect('village_agent/changePassword/'.$id);
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
     
        
       return view('admin.village_agent_reset_password',$result); 
    }
}
