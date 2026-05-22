<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersExport implements FromCollection, WithHeadings
{
    // -------------------------------------------------------
    // Column headers in the Excel file
    // Matches the import format + QR ID for backup/restore
    // -------------------------------------------------------
    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEMILIKI',
            'NO KTP',
            'NO-TELP',
            'UNIT',
            'KAWASAN',
            'QR ID',
        ];
    }

    // -------------------------------------------------------
    // One row per member including their QR token
    // So if the database is ever restored from this file,
    // all printed cards will still work with the same QR codes
    // -------------------------------------------------------
    public function collection()
    {
        return Member::orderBy('nama')->get()->map(function ($member, $index) {
            return [
                'NO'            => $index + 1,
                'NAMA PEMILIKI' => $member->nama,
                'NO KTP'        => $member->no_ktp,
                'NO-TELP'       => $member->no_telp,
                'UNIT'          => $member->unit,
                'KAWASAN'       => $member->kawasan,
                'QR ID'         => $member->qr_token,
            ];
        });
    }
}