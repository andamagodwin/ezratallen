<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::where('user_type', 'user')->select('full_name', 'created_at')->get();
    }

    public function headings(): array

    {

        return [

            'User Name',

            'Joined Date',

        ];
    }
}
