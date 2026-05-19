<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryLog extends Model
{
    // These fields can be mass-assigned
    protected $fillable = [
        'member_id',
        'gate_name',
        'status',
        'deny_reason',
        'scanned_at',
    ];

    // Tell Laravel to treat scanned_at as a Carbon date object
    // so we can do things like $log->scanned_at->format('d M Y')
    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    // -------------------------------------------------------
    // RELATIONSHIP
    // Each log entry belongs to one member
    // -------------------------------------------------------
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}