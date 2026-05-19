<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SampleTemplateExport implements FromCollection, WithHeadings
{
    // -------------------------------------------------------
    // Column headers matching the exact import format
    // -------------------------------------------------------
    public function headings(): array
    {
        return ['NO', 'NAMA PEMILIKI', 'NO KTP', 'NO-TELP', 'UNIT', 'KAWASAN'];
    }

    // -------------------------------------------------------
    // One sample row so the user knows what format to follow
    // -------------------------------------------------------
    public function collection(): Collection
    {
        return collect([
            [
                'NO'            => 1,
                'NAMA PEMILIKI' => 'HALBERG',
                'NO KTP'        => '123456789',
                'NO-TELP'       => '23456789',
                'UNIT'          => 'A-8',
                'KAWASAN'       => 'PALACE',
            ],
        ]);
    }
}