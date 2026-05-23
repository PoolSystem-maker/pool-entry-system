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
    private array $importedKeys  = [];
    private array $importedNames = [];

    // Only these two kawasan are accepted
    private array $validKawasan = ['Diamond Palace', 'Diamond Pavilion'];

    public function model(array $row)
    {
        if (empty($row['nama_pemiliki']) && empty($row['unit'])) {
            return null;
        }

        $nama      = trim((string) ($row['nama_pemiliki'] ?? ''));
        $noKtp     = trim((string) ($row['no_ktp']        ?? ''));
        $noTelp    = trim((string) ($row['no_telp']       ?? '-'));
        $unit      = trim((string) ($row['unit']          ?? ''));
        $cluster = trim((string) ($row['cluster']     ?? ''));
        $kawasan   = trim((string) ($row['kawasan']       ?? ''));

        // Accept existing QR ID if provided, otherwise generate new one
        $qrToken = trim((string) ($row['qr_id'] ?? ''));
        if (empty($qrToken)) {
            $qrToken = (string) Str::uuid();
        }

        // Skip if required fields are empty
        if (empty($nama) || empty($unit) || empty($kawasan)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama ?: '-',
                'no_ktp'    => $noKtp ?: '-',
                'unit'      => $unit ?: '-',
                'cluster' => $cluster ?: '-',
                'kawasan'   => $kawasan ?: '-',
                'reason'    => 'Kolom nama, unit, atau kawasan kosong',
            ];
            return null;
        }

        // Validate kawasan — only Diamond Palace or Diamond Pavilion allowed
        if (!in_array($kawasan, $this->validKawasan)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama,
                'no_ktp'    => $noKtp,
                'unit'      => $unit,
                'cluster' => $cluster,
                'kawasan'   => $kawasan,
                'reason'    => "Kawasan \"{$kawasan}\" tidak valid. Hanya Diamond Palace atau Diamond Pavilion.",
            ];
            return null;
        }

        // Unique key: unit + cluster + kawasan
        $uniqueKey = strtolower($unit . '|' . $cluster . '|' . $kawasan);

        // Check duplicate nama in database
        if (Member::where('nama', $nama)->exists()) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama,
                'no_ktp'    => $noKtp,
                'unit'      => $unit,
                'cluster' => $cluster,
                'kawasan'   => $kawasan,
                'reason'    => "Nama \"{$nama}\" sudah terdaftar di database",
            ];
            return null;
        }

        // Check duplicate nama within this file
        if (in_array(strtolower($nama), $this->importedNames)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama,
                'no_ktp'    => $noKtp,
                'unit'      => $unit,
                'cluster' => $cluster,
                'kawasan'   => $kawasan,
                'reason'    => "Nama \"{$nama}\" duplikat dalam file Excel",
            ];
            return null;
        }

        // Check duplicate unit+cluster+kawasan in database
        if (Member::where('unit', $unit)
            ->where('cluster', $cluster ?: null)
            ->where('kawasan', $kawasan)
            ->exists()) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama,
                'no_ktp'    => $noKtp,
                'unit'      => $unit,
                'cluster' => $cluster,
                'kawasan'   => $kawasan,
                'reason'    => "Unit {$unit} " . ($cluster ? "({$cluster}) " : '') . "di kawasan {$kawasan} sudah terdaftar",
            ];
            return null;
        }

        // Check duplicate within this file
        if (in_array($uniqueKey, $this->importedKeys)) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'nama'      => $nama,
                'no_ktp'    => $noKtp,
                'unit'      => $unit,
                'cluster' => $cluster,
                'kawasan'   => $kawasan,
                'reason'    => "Unit {$unit} " . ($cluster ? "({$cluster}) " : '') . "di kawasan {$kawasan} duplikat dalam file Excel",
            ];
            return null;
        }

        $this->importedKeys[]  = $uniqueKey;
        $this->importedNames[] = strtolower($nama);
        $this->importedCount++;

        return new Member([
            'nama'      => $nama,
            'no_ktp'    => $noKtp,
            'no_telp'   => $noTelp,
            'unit'      => $unit,
            'cluster' => $cluster ?: null,
            'kawasan'   => $kawasan,
            'qr_token'  => $qrToken,
        ]);
    }

    public function getImportedCount(): int  { return $this->importedCount; }
    public function getSkippedCount(): int   { return $this->skippedCount;  }
    public function getSkippedRows(): array  { return $this->skippedRows;   }
}