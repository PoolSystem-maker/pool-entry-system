<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyLimitOverride extends Model
{
    protected $fillable = ['member_id', 'limit', 'date'];

    protected $casts = ['date' => 'date'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}