<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Group;

class indexController extends Controller
{
    public function index()
    {
        return Member::with('getGroup')->get();
        //return Member::find(1)->getGroup();
    }

    public function group()
    {
        return Group::with('getMember')->get();
    }
}
