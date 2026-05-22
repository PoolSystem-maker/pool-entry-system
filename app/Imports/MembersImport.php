<?php

namespace App\Imports;

use App\Models\Member;
use Illuminate\Support\Str;
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
    private array $importedUnits = [];
    private array $importedNames = [];

    public function model(array $row)
    {
        if (empty($row['nama_pemiliki']) && empty($row['unit'])) {
            return null;
        }

        $nama    = trim((string) ($row['nama_pemiliki'] ?? ''));
        $noKtp   = trim((string) ($row['no_ktp']        ?? ''));
        $noTelp  = trim((string) ($row['no_telp']       ?? '-'));
        $unit    = trim((string) ($row['unit']           ?? ''));
        $kawasan = trim((string) ($row['kawasan']        ?? ''));

        // Accept existing QR ID if provided, otherwise generate new one
        // Column header is "QR ID" which WithHeadingRow converts to "qr_id"
        $qrToken = trim((string) ($row['qr_id'] ?? ''));
        if (empty($qrToken)) {
            $qrToken = (string) Str::uuid();
        }

        // Skip if required fields empty
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

        // Check duplicate nama within this file
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

        // Check duplicate unit+kawasan in database
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

        // Check duplicate unit+kawasan within this file
        $unitKey = strtolower($unit . '|' . $kawasan);
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

        $this->importedUnits[] = $unitKey;
        $this->importedNames[] = strtolower($nama);
        $this->importedCount++;

        // Use existing qr_token from Excel if available
        // bypasses the auto-generate in model boot()
        $member = new Member([
            'nama'     => $nama,
            'no_ktp'   => $noKtp,
            'no_telp'  => $noTelp,
            'unit'     => $unit,
            'kawasan'  => $kawasan,
            'qr_token' => $qrToken,
        ]);

        return $member;
    }

    public function getImportedCount(): int  { return $this->importedCount; }
    public function getSkippedCount(): int   { return $this->skippedCount;  }
    public function getSkippedRows(): array  { return $this->skippedRows;   }
}