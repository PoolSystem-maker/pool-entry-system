<?php

namespace App\Exports;

use App\Models\EntryLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyLogExport implements FromCollection, WithHeadings, WithTitle
{
    private int $month;
    private int $year;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    public function title(): string
    {
        return "Log {$this->year}-{$this->month}";
    }

    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL',
            'WAKTU',
            'NAMA',
            'UNIT',
            'CLUSTER',
            'KAWASAN',
            'NO KTP',
            'NO TELP',
            'STATUS',
            'ALASAN DITOLAK',
            'GATE',
        ];
    }

    public function collection()
    {
        // Now includes ALL entries — both granted and denied
        return EntryLog::with('member')
            ->whereMonth('scanned_at', $this->month)
            ->whereYear('scanned_at', $this->year)
            ->orderBy('scanned_at', 'asc')
            ->get()
            ->map(function ($log, $index) {
                return [
                    'NO'             => $index + 1,
                    'TANGGAL'        => $log->scanned_at->format('d/m/Y'),
                    'WAKTU'          => $log->scanned_at->format('H:i:s'),
                    'NAMA'           => $log->member->nama    ?? '-',
                    'UNIT'           => $log->member->unit    ?? '-',
                    'CLUSTER'        => $log->member->cluster ?? '-',
                    'KAWASAN'        => $log->member->kawasan ?? '-',
                    'NO KTP'         => $log->member->no_ktp  ?? '-',
                    'NO TELP'        => $log->member->no_telp ?? '-',
                    'STATUS'         => strtoupper($log->status),
                    'ALASAN DITOLAK' => $log->deny_reason     ?? '-',
                    'GATE'           => $log->gate_name,
                ];
            });
    }
}