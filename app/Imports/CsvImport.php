<?php

namespace App\Imports;

use App\Members;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;



class CsvImport implements ToModel,WithValidation, WithHeadingRow, SkipsOnFailure
{
    use Importable,SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $count = 0;
    public function model(array $row)
    {


        ++$this->count;
        return new Members([

            'title'         =>  $row['title'],
            'full_name'     =>  $row['full_name'],
            'gender'        =>  $row['gender'],
            'birth_date'    =>  $row['birth_date'],
            'phone_number'  =>  $row['phone_number'],
            'email'         =>  $row['email'],
            'marital_status'=>  $row['marital_status'],
            'group_assigned'=>  $row['group_assigned'],
            'home_address'  =>  $row['home_address'],
            'church_id'     =>  Auth::user()->unique_id
        ]);

    }

    public function rules(): array
    {
        return [
           'title'          =>  'required|string',
            'full_name'     =>  'required|string',
            'gender'        =>  'required|string',
            'birth_date'    =>  'required',
            'phone_number'  =>  'required',
            'email'         =>  'unique:members,email',
            'marital_status'=>  'required|string',
            'group_assigned'=>  'required',
            'home_address'  =>  'required|string',
            'church_id'     =>  'trim'
        ];
    }

    public function onFailure(Failure ...$failures){

    //

    }
}
