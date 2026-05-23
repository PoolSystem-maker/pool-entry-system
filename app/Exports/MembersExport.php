<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersExport implements FromCollection, WithHeadings
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

    public function collection()
    {
        return Member::orderBy('nama')->get()->map(function ($member, $index) {
            return [
                'NO'            => $index + 1,
                'NAMA PEMILIKI' => $member->nama,
                'NO KTP'        => $member->no_ktp,
                'NO-TELP'       => $member->no_telp,
                'UNIT'          => $member->unit,
                'CLUSTER'     => $member->cluster ?? '',
                'KAWASAN'       => $member->kawasan,
                'QR ID'         => $member->qr_token,
            ];
        });
    }
}