<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntryLog;
use App\Exports\MonthlyLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MonthlyLogController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year',  now()->year);

        $logs = EntryLog::with('member')
            ->whereMonth('scanned_at', $month)
            ->whereYear('scanned_at', $year)
            ->orderBy('scanned_at', 'asc')
            ->get()
            ->groupBy(fn($log) => $log->scanned_at->format('Y-m-d'));

        $years = range(2024, now()->year);

        return view('admin.monthly-logs.index',
            compact('logs', 'month', 'year', 'years'));
    }

    public function export(Request $request)
    {
        $month    = $request->input('month', now()->month);
        $year     = $request->input('year',  now()->year);
        $filename = "monthly-log-{$year}-{$month}.xlsx";

        return Excel::download(new MonthlyLogExport($month, $year), $filename);
    }

    // -------------------------------------------------------
    // Daily export — printable PDF page for a specific date
    // -------------------------------------------------------
    public function dailyExport(Request $request)
    {
        $date = $request->input('date'); // format: Y-m-d

        $logs = EntryLog::with('member')
            ->whereDate('scanned_at', $date)
            ->orderBy('scanned_at', 'asc')
            ->get();

        $parsedDate = \Carbon\Carbon::parse($date);

        return view('admin.monthly-logs.daily-export',
            compact('logs', 'date', 'parsedDate'));
    }
}