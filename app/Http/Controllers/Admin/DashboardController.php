<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\EntryLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // -------------------------------------------------------
    // Shows the admin dashboard with live stats
    // -------------------------------------------------------
    public function index()
    {
        // Total number of members in the system
        $totalMembers = Member::count();

        // Total members grouped by kawasan (area/zone)
        // e.g. ['PALACE' => 10, 'PAVILION' => 5]
        $membersByKawasan = Member::select('kawasan', DB::raw('count(*) as total'))
            ->groupBy('kawasan')
            ->pluck('total', 'kawasan');

        // Total granted entries today
        $todayEntryCount = EntryLog::where('status', 'granted')
            ->whereDate('scanned_at', today())
            ->count();

        // How many members have already hit their daily limit today
        // We count members where their granted scans today >= their daily_limit
        $membersAtLimit = Member::whereHas('entryLogs', function ($query) {
            $query->where('status', 'granted')
                ->whereDate('scanned_at', today());
        })
        ->get()
        ->filter(fn($member) => $member->todayGrantedCount() >= $member->daily_limit)
        ->count();

        // Recent 10 scan attempts (both granted and denied)
        $recentScans = EntryLog::with('member')
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalMembers',
            'membersByKawasan',
            'todayEntryCount',
            'membersAtLimit',
            'recentScans',
        ));
    }
}