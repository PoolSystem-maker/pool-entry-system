<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntryLog;
use App\Models\Member;
use Illuminate\Http\Request;

class EntryLogController extends Controller
{
    // -------------------------------------------------------
    // Show all scan history with filters
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $query = EntryLog::with('member')->orderBy('scanned_at', 'desc');

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->date);
        }

        // Filter by member name
        if ($request->filled('member_name')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->member_name}%");
            });
        }

        // Filter by status (granted or denied)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.entry-logs.index', compact('logs'));
    }
}