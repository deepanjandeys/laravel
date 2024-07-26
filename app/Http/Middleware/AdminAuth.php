<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('ADMIN_LOGIN') && $request->session()->get('ADMIN_TYPE')=='1') 
        {
        
        }
        else
        {
            $request->session()->flash('error','access denied');
            $typeName=session()->get('typeName');
            if($typeName=='')
                $typeName='admin';

            return redirect($typeName);
        }

        return $next($request);

    }
}
