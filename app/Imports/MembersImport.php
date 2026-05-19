<?php

namespace App\Imports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class MembersImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    // Keep track of how many rows were imported and skipped
    private int $importedCount  = 0;
    private int $duplicateCount = 0;

    // -------------------------------------------------------
    // This runs for every row in the Excel file
    // The $row array keys match the Excel column headers
    // Our expected headers: NO, NAMA PEMILIKI, NO KTP, NO-TELP, UNIT, KAWASAN
    // WithHeadingRow converts them to snake_case automatically:
    // no, nama_pemiliki, no_ktp, no_telp, unit, kawasan
    // -------------------------------------------------------
    public function model(array $row)
    {
        // Skip rows where no_ktp is empty
        if (empty($row['no_ktp'])) {
            return null;
        }

        // Check for duplicate no_ktp
        $exists = Member::where('no_ktp', $row['no_ktp'])->exists();

        if ($exists) {
            $this->duplicateCount++;
            return null;
        }

        $this->importedCount++;

        // qr_token is auto-generated in the Member model boot() method
        return new Member([
            'nama'    => $row['nama_pemiliki'],
            'no_ktp'  => $row['no_ktp'],
            'no_telp' => $row['no_telp'],
            'unit'    => $row['unit'],
            'kawasan' => $row['kawasan'],
        ]);
    }

    // -------------------------------------------------------
    // Getters so the controller can report results
    // -------------------------------------------------------
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getDuplicateCount(): int
    {
        return $this->duplicateCount;
    }
}