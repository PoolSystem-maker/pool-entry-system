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

        // Now shows ALL entries — both granted and denied
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
}