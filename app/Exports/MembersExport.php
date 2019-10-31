<?php

namespace App\Exports;

use App\Members;
use Maatwebsite\Excel\Concerns\FromCollection;

class MembersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Members::all();
    }
}
