<?php

namespace App\Exports;

use App\Models\Bill;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BillExport implements FromView //FromCollection
{
    public function view(): View
    {
        $bills=Bill::all();
        return view('admin.bill-export', [
            'bills' => $bills
        ]);
    }
}
