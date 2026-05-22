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
            'nama'    => 'required|string|max:255|unique:members,nama',
            'no_ktp'  => 'required|string',
            'no_telp' => 'required|string|max:20',
            'unit'    => 'required|string|max:50',
            'kawasan' => 'required|string|max:100',
            // unit+kawasan combination must be unique
            'unit'    => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('members')->where(fn($q) =>
                    $q->where('kawasan', request('kawasan'))
                ),
            ],
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
            'nama'        => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('members', 'nama')->ignore($member->id),
            ],
            'no_ktp'      => 'required|string',
            'no_telp'     => 'required|string|max:20',
            'kawasan'     => 'required|string|max:100',
            'daily_limit' => 'required|integer|min:1|max:100',
            'is_active'   => 'boolean',
            'unit'        => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('members')
                    ->where(fn($q) => $q->where('kawasan', request('kawasan')))
                    ->ignore($member->id),
            ],
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

        $imported = $import->getImportedCount();
        $skipped  = $import->getSkippedCount();
        $rows     = $import->getSkippedRows();

        // Store skipped rows in session to display on next page
        session(['import_skipped' => $rows]);

        return redirect()->route('admin.members.index')
            ->with('success',
                "{$imported} member berhasil diimport. {$skipped} dilewati.");
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
// -------------------------------------------------------
// Download a single member card as SVG
// -------------------------------------------------------
    public function printCard(Member $member)
    {
        $qrSvg = QrCode::format('svg')
            ->size(150)
            ->generate($member->qr_token);

        $cardSvg = $this->buildCardSvg($member, $qrSvg);

        $filename = $member->unit . '-' . $member->kawasan . '.svg';

        return response($cardSvg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // -------------------------------------------------------
    // Download the member's QR code as an SVG file
    // SVG is scalable and prints at any size without blur
    // -------------------------------------------------------
    public function downloadQr(Member $member)
    {
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($member->qr_token);

        return response($qrCode, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-' . $member->unit . '.svg"',
        ]);
    }

    // -------------------------------------------------------
    // Print all member cards as a printable page
    // -------------------------------------------------------
    public function printAllCards()
    {
        $members = Member::orderBy('kawasan')->orderBy('nama')->get();

        $qrCodes = $members->mapWithKeys(function ($member) {
            return [
                $member->id => QrCode::format('svg')
                    ->size(150)
                    ->generate($member->qr_token)
            ];
        });

        return view('admin.members.print-all-cards', compact('members', 'qrCodes'));
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
    // Download all member QR cards as a ZIP of SVG files
    // Each file named by unit-kawasan
    // -------------------------------------------------------
    public function downloadAllCards()
    {
        $members = Member::orderBy('kawasan')->orderBy('nama')->get();

        // Create a temporary ZIP file
        $zipPath = storage_path('app/temp-cards.zip');

        // Delete old temp file if exists
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($members as $member) {
            // Generate SVG card for this member
            $qrSvg = QrCode::format('svg')
                ->size(150)
                ->generate($member->qr_token);

            // Build a self-contained SVG card (CR80 size: 85.6mm x 54mm)
            $cardSvg = $this->buildCardSvg($member, $qrSvg);

            // Filename: unit-kawasan.svg e.g. "A-8-PALACE.svg"
            $filename = $member->unit . '-' . $member->kawasan . '.svg';

            $zip->addFromString($filename, $cardSvg);
        }

        $zip->close();

        return response()->download($zipPath, 'all-member-cards.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    // -------------------------------------------------------
    // Build a self-contained SVG card for one member
    // CR80 size: 323px x 204px (85.6mm x 54mm at 96dpi)
    // -------------------------------------------------------
    private function buildCardSvg(Member $member, string $qrSvg): string
    {
        // Strip XML declaration and outer SVG tags from QR code
        $qrInner = preg_replace('/<\?xml[^>]*\?>\s*/i', '', $qrSvg);
        $qrInner = preg_replace('/<svg[^>]*>/i', '', $qrInner);
        $qrInner = str_replace('</svg>', '', $qrInner);
        $qrInner = trim($qrInner);

        $nama    = htmlspecialchars($member->nama,    ENT_XML1, 'UTF-8');
        $unit    = htmlspecialchars($member->unit,    ENT_XML1, 'UTF-8');
        $kawasan = htmlspecialchars($member->kawasan, ENT_XML1, 'UTF-8');
        $id      = $member->id;

        // QR position and size
        $qrBoxX    = 182;
        $qrBoxY    = 42;
        $qrBoxSize = 120;
        $qrPadding = 4;
        $qrScale   = ($qrBoxSize - ($qrPadding * 2)) / 150;
        $qrInnerX  = $qrBoxX + $qrPadding;
        $qrInnerY  = $qrBoxY + $qrPadding;

        return <<<SVG
    <?xml version="1.0" encoding="UTF-8"?>
    <svg xmlns="http://www.w3.org/2000/svg"
        width="323" height="204" viewBox="0 0 323 204">
    <defs>
        <linearGradient id="cardBg" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%"   stop-color="#1e3a8a"/>
        <stop offset="60%"  stop-color="#1d4ed8"/>
        <stop offset="100%" stop-color="#0ea5e9"/>
        </linearGradient>
        <clipPath id="cardClip">
        <rect width="323" height="204" rx="15"/>
        </clipPath>
    </defs>

    <!-- Card background -->
    <rect width="323" height="204" rx="15" fill="url(#cardBg)"/>

    <!-- Decorative circle top-right -->
    <circle cx="290" cy="-10" r="80"
        fill="white" fill-opacity="0.07" clip-path="url(#cardClip)"/>

    <!-- Decorative circle bottom-left -->
    <circle cx="70" cy="180" r="55"
        fill="white" fill-opacity="0.05" clip-path="url(#cardClip)"/>

    <!-- Top-right label -->
    <text x="300" y="16"
        font-family="Arial,sans-serif" font-size="7"
        fill="rgba(255,255,255,0.5)" letter-spacing="1"
        text-anchor="end">POOL ENTRY PASS</text>

    <!-- Left: POOL ENTRY PASS small label -->
    <text x="19" y="60"
        font-family="Arial,sans-serif" font-size="7"
        fill="rgba(255,255,255,0.7)" letter-spacing="1">
        POOL ENTRY PASS
    </text>

    <!-- Left: Member name -->
    <text x="19" y="76"
        font-family="Arial,sans-serif" font-size="19"
        font-weight="bold" fill="white">{$nama}</text>

    <!-- Left: Unit -->
    <text x="19" y="100"
        font-family="Arial,sans-serif" font-size="14"
        font-weight="600" fill="#bfdbfe">Unit {$unit}</text>

    <!-- Left: Kawasan -->
    <text x="19" y="120"
        font-family="Arial,sans-serif" font-size="11"
        fill="rgba(255,255,255,0.7)" letter-spacing="0.5">{$kawasan}</text>

    <!-- Left: ID — right below kawasan -->
    <text x="19" y="138"
        font-family="monospace,Arial" font-size="9"
        fill="rgba(255,255,255,0.5)">ID #{$id}</text>

    <!-- QR white background box -->
    <rect x="{$qrBoxX}" y="{$qrBoxY}"
        width="{$qrBoxSize}" height="{$qrBoxSize}"
        rx="8" fill="white"/>

    <!-- QR code content -->
    <g transform="translate({$qrInnerX}, {$qrInnerY}) scale({$qrScale})">
        {$qrInner}
    </g>

    </svg>
    SVG;
    }
}