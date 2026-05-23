<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEMILIKI',
            'NO KTP',
            'NO-TELP',
            'UNIT',
            'CLUSTER',
            'KAWASAN',
            'QR ID',
        ];
    }

    public function collection(): Collection
    {
        return collect([
            [
                'NO'            => 1,
                'NAMA PEMILIKI' => 'HALBERG',
                'NO KTP'        => '123456789',
                'NO-TELP'       => '23456789',
                'UNIT'          => 'A-8',
                'CLUSTER'     => 'TRILIAN',
                'KAWASAN'       => 'Diamond Palace',
                'QR ID'         => '',
            ],
            [
                'NO'            => 2,
                'NAMA PEMILIKI' => 'HALBERG2',
                'NO KTP'        => '12345678',
                'NO-TELP'       => '12345678',
                'UNIT'          => 'B-7',
                'CLUSTER'     => '',
                'KAWASAN'       => 'Diamond Pavilion',
                'QR ID'         => '',
            ],
        ]);
    }
}