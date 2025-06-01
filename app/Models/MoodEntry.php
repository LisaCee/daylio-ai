<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class MoodEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'mood_level',
        'entry_date',
        'notes',
        'activities',
        'entry_time',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'entry_date' => 'date',
        'entry_time' => 'datetime:H:i',
        'activities' => 'array',
    ];

    /**
     * Get the user that owns the mood entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mood level as a descriptive string.
     */
    public function getMoodDescriptionAttribute(): string
    {
        return match ($this->mood_level) {
            1 => 'Very Bad',
            2 => 'Bad',
            3 => 'Neutral',
            4 => 'Good',
            5 => 'Very Good',
            default => 'Unknown'
        };
    }

    /**
     * Get the mood emoji.
     */
    public function getMoodEmojiAttribute(): string
    {
        return match ($this->mood_level) {
            1 => '😞',
            2 => '😔',
            3 => '😐',
            4 => '😊',
            5 => '😁',
            default => '❓'
        };
    }

    /**
     * Scope to get entries for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get entries for current month.
     */
    public function scopeCurrentMonth($query)
    {
        $now = Carbon::now();
        return $query->whereYear('entry_date', $now->year)
            ->whereMonth('entry_date', $now->month);
    }
}
