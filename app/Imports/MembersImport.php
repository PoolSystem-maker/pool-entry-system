<?php

namespace App\Imports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class MembersImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private int   $importedCount = 0;
    private int   $skippedCount  = 0;
    private array $skippedRows   = [];

    // Track combinations imported in this file session
    private array $importedUnits = [];
    private array $importedNames = [];

    public function model(array $row)
    {
        // Skip completely empty rows
        if (empty($row['nama_pemiliki']) && empty($row['unit'])) {
            return null;
        }

        $nama    = trim((string) ($row['nama_pemiliki'] ?? ''));
        $noKtp   = trim((string) ($row['no_ktp']        ?? ''));
        $noTelp  = trim((string) ($row['no_telp']       ?? '-'));
        $unit    = trim((string) ($row['unit']           ?? ''));
        $kawasan = trim((string) ($row['kawasan']        ?? ''));

        // Skip if required fields are empty
        if (empty($nama) || empty($unit) || empty($kawasan)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'    => $nama ?: '-',
                'no_ktp'  => $noKtp ?: '-',
                'unit'    => $unit ?: '-',
                'kawasan' => $kawasan ?: '-',
                'reason'  => 'Kolom nama, unit, atau kawasan kosong',
            ];
            return null;
        }

        // Check duplicate nama in database
        if (Member::where('nama', $nama)->exists()) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'    => $nama,
                'no_ktp'  => $noKtp,
                'unit'    => $unit,
                'kawasan' => $kawasan,
                'reason'  => "Nama \"{$nama}\" sudah terdaftar di database",
            ];
            return null;
        }

        // Check duplicate nama within this import file
        if (in_array(strtolower($nama), $this->importedNames)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'    => $nama,
                'no_ktp'  => $noKtp,
                'unit'    => $unit,
                'kawasan' => $kawasan,
                'reason'  => "Nama \"{$nama}\" duplikat dalam file Excel",
            ];
            return null;
        }

        // Check duplicate unit+kawasan combination in database
        $unitKey = strtolower($unit . '|' . $kawasan);
        if (Member::where('unit', $unit)->where('kawasan', $kawasan)->exists()) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'    => $nama,
                'no_ktp'  => $noKtp,
                'unit'    => $unit,
                'kawasan' => $kawasan,
                'reason'  => "Unit {$unit} di kawasan {$kawasan} sudah terdaftar",
            ];
            return null;
        }

        // Check duplicate unit+kawasan within this import file
        if (in_array($unitKey, $this->importedUnits)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'    => $nama,
                'no_ktp'  => $noKtp,
                'unit'    => $unit,
                'kawasan' => $kawasan,
                'reason'  => "Unit {$unit} di kawasan {$kawasan} duplikat dalam file Excel",
            ];
            return null;
        }

        // All checks passed — import this row
        $this->importedUnits[] = $unitKey;
        $this->importedNames[] = strtolower($nama);
        $this->importedCount++;

        return new Member([
            'nama'    => $nama,
            'no_ktp'  => $noKtp,
            'no_telp' => $noTelp,
            'unit'    => $unit,
            'kawasan' => $kawasan,
        ]);
    }

    public function getImportedCount(): int  { return $this->importedCount; }
    public function getSkippedCount(): int   { return $this->skippedCount;  }
    public function getSkippedRows(): array  { return $this->skippedRows;   }
}