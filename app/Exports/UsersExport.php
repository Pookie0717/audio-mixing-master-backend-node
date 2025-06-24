<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Email',
            'Created At'
        ];
    }
    public function map($users): array
    {
        // Map the data in the order you want it to appear
        return [
            $users->id,
            $users->email,
            $users->created_at->format('d/m/Y'),
        ];
    }
}
