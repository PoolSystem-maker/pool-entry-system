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

    // -------------------------------------------------------
    // Sheet title in the Excel file
    // -------------------------------------------------------
    public function title(): string
    {
        return "Log {$this->year}-{$this->month}";
    }

    // -------------------------------------------------------
    // Column headers
    // -------------------------------------------------------
    public function headings(): array
    {
        return ['NO', 'TANGGAL', 'NAMA', 'UNIT', 'KAWASAN', 'WAKTU MASUK'];
    }

    // -------------------------------------------------------
    // Only granted entries for the selected month
    // -------------------------------------------------------
    public function collection()
    {
        return EntryLog::with('member')
            ->where('status', 'granted')
            ->whereMonth('scanned_at', $this->month)
            ->whereYear('scanned_at', $this->year)
            ->orderBy('scanned_at', 'asc')
            ->get()
            ->map(function ($log, $index) {
                return [
                    'NO'          => $index + 1,
                    'TANGGAL'     => $log->scanned_at->format('d/m/Y'),
                    'NAMA'        => $log->member->nama ?? '-',
                    'UNIT'        => $log->member->unit ?? '-',
                    'KAWASAN'     => $log->member->kawasan ?? '-',
                    'WAKTU MASUK' => $log->scanned_at->format('H:i:s'),
                ];
            });
    }
}