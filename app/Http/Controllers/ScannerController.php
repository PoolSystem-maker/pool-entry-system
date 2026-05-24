<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\EntryLog;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    // -------------------------------------------------------
    // Shows the QR scanner page at the root URL "/"
    // -------------------------------------------------------
    public function index()
    {
        return view('scanner.index');
    }

    // -------------------------------------------------------
    // Called via AJAX when the scanner detects a QR code
    // -------------------------------------------------------
    public function scan(Request $request)
    {
        $token = $request->input('token');

        // CHECK 1 — Does this QR token exist?
        $member = Member::where('qr_token', $token)->first();

        if (!$member) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'QR code tidak dikenali.',
            ]);
        }

        // Helper to build member info array including cluster
        $memberInfo = [
            'nama'    => $member->nama,
            'unit'    => $member->unit,
            'cluster' => $member->cluster,
            'kawasan' => $member->kawasan,
        ];

        // CHECK 2 — Is the member active?
        if (!$member->is_active) {
            EntryLog::create([
                'member_id'   => $member->id,
                'gate_name'   => 'Main Gate',
                'status'      => 'denied',
                'deny_reason' => 'Member tidak aktif.',
                'scanned_at'  => now(),
            ]);

            return response()->json([
                'status' => 'denied',
                'reason' => 'Member tidak aktif.',
                'member' => $memberInfo,
            ]);
        }

        // CHECK 3 — Has the member hit their daily limit?
        if (!$member->canEnterToday()) {
            $limit = $member->todayLimit();

            EntryLog::create([
                'member_id'   => $member->id,
                'gate_name'   => 'Main Gate',
                'status'      => 'denied',
                'deny_reason' => "Batas akses harian telah tercapai. ({$limit}/{$limit})",
                'scanned_at'  => now(),
            ]);

            return response()->json([
                'status' => 'denied',
                'reason' => "Batas akses harian telah tercapai. ({$limit}/{$limit})",
                'member' => $memberInfo,
            ]);
        }

        // ALL CHECKS PASSED — Grant entry
        EntryLog::create([
            'member_id'   => $member->id,
            'gate_name'   => 'Main Gate',
            'status'      => 'granted',
            'deny_reason' => null,
            'scanned_at'  => now(),
        ]);

        $member->refresh();
        $remaining = $member->remainingEntriesToday();

        return response()->json([
            'status'    => 'granted',
            'member'    => $memberInfo,
            'remaining' => max(0, $remaining),
        ]);
    }
}