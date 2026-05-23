<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Member extends Model
{
    protected $fillable = [
        'nama',
        'no_ktp',
        'no_telp',
        'unit',
        'cluster',
        'kawasan',
        'qr_token',
        'is_active',
        'daily_limit',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'daily_limit' => 'integer',
    ];

    // -------------------------------------------------------
    // RELATIONSHIPS
    // -------------------------------------------------------
    public function entryLogs()
    {
        return $this->hasMany(EntryLog::class);
    }

    public function dailyLimitOverrides()
    {
        return $this->hasMany(DailyLimitOverride::class);
    }

    // -------------------------------------------------------
    // Get today's effective limit
    // Checks if there's a manual override for today first
    // Falls back to the default daily_limit if not
    // -------------------------------------------------------
    public function todayLimit(): int
    {
        $override = $this->dailyLimitOverrides()
            ->whereDate('date', today())
            ->first();

        return $override ? $override->limit : $this->daily_limit;
    }

    // -------------------------------------------------------
    // Count granted scans today
    // -------------------------------------------------------
    public function todayGrantedCount(): int
    {
        return $this->entryLogs()
            ->where('status', 'granted')
            ->whereDate('scanned_at', today())
            ->count();
    }

    // -------------------------------------------------------
    // Check if member can enter today
    // Uses todayLimit() so overrides are respected
    // -------------------------------------------------------
    public function canEnterToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->todayGrantedCount() < $this->todayLimit();
    }

    // -------------------------------------------------------
    // Remaining entries today
    // -------------------------------------------------------
    public function remainingEntriesToday(): int
    {
        $remaining = $this->todayLimit() - $this->todayGrantedCount();
        return max(0, $remaining);
    }

    // -------------------------------------------------------
    // Auto-generate QR token on create
    // -------------------------------------------------------
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($member) {
            // Only generate QR token if one wasn't provided
            if (empty($member->qr_token)) {
                $member->qr_token = (string) Str::uuid();
            }
        });
    }
}