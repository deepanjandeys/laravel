<?php

namespace App\Http\Controllers;
use App\Exports\UserExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
//use Maatwebsite\Excel\Facades\Excel;

class UserExportController extends Controller
{
    public function export(Excel $excel)
    {
        //return new UserExport;
        //return (new UserExport)->download('new.xlsx');
        //return Excel::download(new UserExport,'users.xlsx');
        return $excel->download(new UserExport,'users.xlsx');
    }
}
