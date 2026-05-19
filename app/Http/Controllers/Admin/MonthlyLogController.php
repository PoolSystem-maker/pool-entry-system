<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntryLog;
use App\Exports\MonthlyLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MonthlyLogController extends Controller
{
    // -------------------------------------------------------
    // Show granted entries grouped by day for a selected month
    // -------------------------------------------------------
    public function index(Request $request)
    {
        // Default to current month and year if not specified
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        // Get all granted entries for the selected month
        // grouped by day
        $logs = EntryLog::with('member')
            ->where('status', 'granted')
            ->whereMonth('scanned_at', $month)
            ->whereYear('scanned_at', $year)
            ->orderBy('scanned_at', 'asc')
            ->get()
            ->groupBy(fn($log) => $log->scanned_at->format('Y-m-d'));

        // Build a list of years for the filter dropdown
        // from 2024 up to current year
        $years = range(2024, now()->year);

        return view('admin.monthly-logs.index', compact('logs', 'month', 'year', 'years'));
    }

    // -------------------------------------------------------
    // Export the monthly log as an Excel file
    // Only granted entries for the selected month
    // -------------------------------------------------------
    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $filename = "monthly-log-{$year}-{$month}.xlsx";

        return Excel::download(new MonthlyLogExport($month, $year), $filename);
    }
}