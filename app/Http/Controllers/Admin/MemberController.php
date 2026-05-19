<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Imports\MembersImport;
use App\Exports\MembersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberController extends Controller
{
    // -------------------------------------------------------
    // Show all members with search and today's scan count
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $query = Member::query();

        // Search by name, unit, or kawasan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('kawasan', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('nama')->paginate(20)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    // -------------------------------------------------------
    // Show the add new member form
    // -------------------------------------------------------
    public function create()
    {
        return view('admin.members.create');
    }

    // -------------------------------------------------------
    // Save a new member to the database
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:255',
            'no_ktp'  => 'required|string|unique:members,no_ktp',
            'no_telp' => 'required|string|max:20',
            'unit'    => 'required|string|max:50',
            'kawasan' => 'required|string|max:100',
        ]);

        // qr_token is auto-generated in the Member model boot() method
        Member::create($request->only([
            'nama', 'no_ktp', 'no_telp', 'unit', 'kawasan'
        ]));

        return redirect()->route('admin.members.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // Show a single member's details and QR code
    // -------------------------------------------------------
    public function show(Member $member)
    {
        // Generate the QR code as SVG to display on screen
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->generate($member->qr_token);

        return view('admin.members.show', compact('member', 'qrCode'));
    }

    // -------------------------------------------------------
    // Show the edit member form
    // -------------------------------------------------------
    public function edit(Member $member)
    {
        return view('admin.members.edit', compact('member'));
    }

    // -------------------------------------------------------
    // Update member details in the database
    // -------------------------------------------------------
    public function update(Request $request, Member $member)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'no_ktp'      => 'required|string|unique:members,no_ktp,' . $member->id,
            'no_telp'     => 'required|string|max:20',
            'unit'        => 'required|string|max:50',
            'kawasan'     => 'required|string|max:100',
            'daily_limit' => 'required|integer|min:1|max:20',
            'is_active'   => 'boolean',
        ]);

        $member->update([
            'nama'        => $request->nama,
            'no_ktp'      => $request->no_ktp,
            'no_telp'     => $request->no_telp,
            'unit'        => $request->unit,
            'kawasan'     => $request->kawasan,
            'daily_limit' => $request->daily_limit,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Member berhasil diupdate.');
    }

    // -------------------------------------------------------
    // Delete a member from the database
    // -------------------------------------------------------
    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('admin.members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    // -------------------------------------------------------
    // Toggle a member's active/inactive status
    // -------------------------------------------------------
    public function toggle(Member $member)
    {
        $member->update(['is_active' => !$member->is_active]);

        $status = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Member berhasil {$status}.");
    }

    // -------------------------------------------------------
    // Regenerate the QR token for a member
    // This invalidates the old QR code
    // -------------------------------------------------------
    public function regenerateQr(Member $member)
    {
        $member->update(['qr_token' => Str::uuid()]);

        return back()->with('success', 'QR code berhasil digenerate ulang.');
    }

    // -------------------------------------------------------
    // Export all members as an Excel file
    // -------------------------------------------------------
    public function export()
    {
        return Excel::download(new MembersExport, 'members.xlsx');
    }

    // -------------------------------------------------------
    // Import members from an uploaded Excel file
    // -------------------------------------------------------
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $import = new MembersImport;
        Excel::import($import, $request->file('file'));

        $imported   = $import->getImportedCount();
        $duplicates = $import->getDuplicateCount();

        return back()->with('success',
            "{$imported} member berhasil diimport. {$duplicates} duplikat dilewati."
        );
    }

    // -------------------------------------------------------
    // Download a sample Excel template for importing members
    // -------------------------------------------------------
    public function sampleTemplate()
    {
        return Excel::download(new \App\Exports\SampleTemplateExport, 'sample-template.xlsx');
    }

    // -------------------------------------------------------
    // Show the print-ready ID card page for a member
    // -------------------------------------------------------
    public function printCard(Member $member)
    {
        $qrCode = QrCode::format('svg')
            ->size(150)
            ->generate($member->qr_token);

        return view('admin.members.print-card', compact('member', 'qrCode'));
    }

    // -------------------------------------------------------
    // Download the member's QR code as a PNG image
    // -------------------------------------------------------
    public function downloadQr(Member $member)
    {
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($member->qr_token);

        return response($qrCode, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => "attachment; filename=\"qr-{$member->unit}.png\"",
        ]);
    }

    // -------------------------------------------------------
    // Set a manual daily limit override for today only
    // Tomorrow it automatically reverts to default daily_limit
    // -------------------------------------------------------
    public function overrideLimit(Request $request, Member $member)
    {
        $request->validate([
            'override_limit' => 'required|integer|min:1|max:100',
        ]);

        // Use updateOrCreate so running it twice just updates
        \App\Models\DailyLimitOverride::updateOrCreate(
            [
                'member_id' => $member->id,
                'date'      => today(),
            ],
            [
                'limit' => $request->override_limit,
            ]
        );

        return back()->with('success',
            "Batas akses hari ini untuk {$member->nama} diset ke {$request->override_limit}x."
        );
    }

    // -------------------------------------------------------
    // Print all member cards as a printable page
    // -------------------------------------------------------
    public function printAllCards()
    {
        $members = Member::orderBy('kawasan')->orderBy('nama')->get();

        $qrCodes = $members->mapWithKeys(function ($member) {
            return [
                $member->id => QrCode::format('svg')->size(150)->generate($member->qr_token)
            ];
        });

        return view('admin.members.print-all-cards', compact('members', 'qrCodes'));
    }
}