<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SalesAgent;
use App\Models\VillageAgent;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        
        if ($request->session()->has('ADMIN_LOGIN')) 
        {
            return redirect('admin/dashboard');
        }
        else
        {
           $result['type']=1; 
           return view('admin.login',$result);
        }
        
        
    }

    public function auth(Request $request)
    {
        $name = $request->post('name');
        $type = $request->post('type');
        $password = $request->post('password');
        $result = Admin::where(['name'=>$name])->where(['type'=>$type])->first();
        if (isset($result)) 
        {
            if (Hash::check($request->post('password'),$result->password)) 
            {
                
                if($result->isApproved=='0')
                {
                    $request->session()->flash('error','your User id is not approved ');
                    return redirect('admin');
                }

                if($result->status=='0')
                {
                    $request->session()->flash('error','your User id is inactive');
                    return redirect('admin');
                }

                $request->session()->put('ADMIN_LOGIN','true');
                $request->session()->put('ADMIN_ID',$result->id);
                $request->session()->put('ADMIN_TYPE',$result->type);
                $request->session()->put('ADMIN_NAME',$result->name);
                $request->session()->put('ADMIN_IMAGE',$result->image);
                $request->session()->put('ADMIN_REFER_ID',$result->refer_id);
                $request->session()->put('ADMIN_EMAIL',$result->email);


                $typeName='';
                if($result->type=='1')
                {
                $typeName='admin';
                $request->session()->put('ADMIN_REFER_NAME','admin');
                $request->session()->put('ADMIN_LOGIN','true');
                }
                elseif ($result->type=='2')
                {
                $request->session()->put('BRANCH_LOGIN','true');
                $typeName='branch';
                } 
                elseif ($result->type=='3')
                {
                $request->session()->put('SALE_AGENT_LOGIN','true');
                $typeName='sale_agent';

                $sale_agent=SalesAgent::find($result->refer_id);
                $request->session()->put('ADMIN_REFER_NAME',$sale_agent->name);
                
                } 
                elseif ($result->type=='4')
                {
                $request->session()->put('VILLAGE_AGENT_LOGIN','true');
                $typeName='village_agent';
                $villageAgent=VillageAgent::find($result->refer_id);
                $village = Village::find($villageAgent->village_id);
                $request->session()->put('ADMIN_REFER_NAME',$villageAgent->name.' / '.$village->name);
                
                } 
                  
                $request->session()->put('typeName',$typeName);
                $request->session()->put('ADMIN_ID',$result->id);
                return redirect($typeName.'/dashboard');

                //return redirect('admin/dashboard');
            }
            else
            {
                $request->session()->flash('error','Please enter correct password');

                $typeName='';
                if($type=='1')
                {
                $typeName='admin';
                }
                elseif ($result->type=='2')
                {
                $typeName='branch';
                } 
                elseif ($result->type=='3')
                {
                $typeName='sale_agent';
                } 
                elseif ($result->type=='4')
                {
                $typeName='village_agent';
                } 
                
                return redirect($typeName);
            }
           
        }
        else
        {
            $request->session()->flash('error','Please enter valid login details');
            if($type=='1')
                return redirect('admin');
            else if($type=='3')
                return redirect('sale_agent');
            else if($type=='4')
                return redirect('village_agent');
        }
        
    }
    public function dashboard()
    {
        return view('admin.dashboard'); 
    }
    
    public function forgetPassword(Request $request)
    {
        $id=0;
        $name=$request->post('name');
        $result = Admin::where(['name'=>$name])->first();
        if (isset($result))
        {
           $email =  $result->email; 
           $id    =  $result->id;
           if($email=='')
            {
            $request->session()->flash('error','Your email id is not set,we unable to send password reset link to your email, ask Admin to create a password reset link to you');
            return redirect('admin/forget_password');   
            }

           $subject = urlencode("Forget password");
           $fun = new FunctionsController();
           $enId = $fun->encrypt($id);
           $t_url= $fun->get_tiny_url("http://127.0.0.1:8000/admin/ResetPassword/".$enId);


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
       return redirect('admin/forget_password_success/'.$id);
     
        }
        else
        {
            $request->session()->flash('error','name number not exist');
            return redirect('admin/forget_password');
        }
        
    }
    
    public function forget_password_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::find($id);
         //print_r($arr);

            if($arr->count() >0)
            {
            $fun = new FunctionsController();
            $email = $fun->maskEmail($arr->email);
            $result['email']= $email;
            }
        }
        else
        {
            $result['email']= '';
        }
       return view('admin.admin_forget_password_success',$result); 
    }

public function password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::find($id);
            //$arr=Admin::where('refer_id','=',$arr[0]->id)->get();

            $result['id']= $arr->id;
            $result['name']= $arr->name;
            
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
            $model = Admin::find($id); 
        if (isset($model))
        {
            
            //if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('admin/password_change_success/'.$id);
            }
           /* else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('admin/password_change_success/'.$id)->with('error',$msg);

            }
            */
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('admin/changePassword/'.$id);
        }
       }
       
        
    }

public function admin_password_reset(Request $request,$id='')
    {
            
        if ($id!='') 
        {
           
           $fun = new FunctionsController();
            $id = (int)$fun->decrypt($id);
            

            $arr = Admin::find($id);
            //echo "Count ".$arr->count();
            if($arr->count()==0)
            {
                return redirect('no-access');       
            }
            $result['id']= $arr->id;
            $result['name']= $arr->name;
        }
     
        
       return view('admin.admin_reset_password',$result); 
    }
    public function admin_password_change_success(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::find($id);
            $result['id']= $arr->id;
            $result['name']= $arr->name;
            
        }
       return view('admin.admin_password_change_success',$result); 
    }
    public function manage_admin_password_reset(Request $request)
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
            $model = Admin::find($id); 

        if (isset($model))
        {
            $model->password = Hash::make($newPassword);
            $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
    return redirect('admin/password_change_success/'.$id);
            
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            return redirect('admin/ResetPassword/'.$id);
        }
       }
       
        
    }
   public function admin_password_change(Request $request)
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
            $model = Admin::find($id); 

        if (isset($model))
        {
            if (Hash::check($password,$model->password)) 
            {       
           $model->password = Hash::make($newPassword);
           $msg = 'User password reset';
            $model->save();
            $request->session()->flash('message',$msg);
            return redirect('admin/password_change_success/'.$id);
            }
            else
            {
            $msg = 'User current password Not matched';   
            //$request->session()->flash('message',$msg);
            return redirect('admin/password_change/'.$id)->with('error',$msg);

            }
        }
        else 
        {
            $msg = 'User Not found';   
            $request->session()->flash('message',$msg);
            //return redirect('admin/changePassword/'.$id);
        }
       }   
    }

public function reset(Request $request,$id='')
    {
        if ($id>0) 
        {
            $arr = Admin::where(['id'=>$id])->get();
            $result['id']= $arr[0]->id;
            $result['name']= $arr[0]->name;
            
        }
     
        
       return view('admin.admin_reset_password',$result); 
    }
}
