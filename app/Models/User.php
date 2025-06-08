<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the mood entries for the user.
     */
    public function moodEntries(): HasMany
    {
        return $this->hasMany(MoodEntry::class);
    }

    /**
     * Get the latest mood entry for the user.
     */
    public function latestMoodEntry()
    {
        return $this->moodEntries()->latest('entry_date')->first();
    }

    /**
     * Get the average mood level for the user.
     */
    public function averageMoodLevel(): float
    {
        return $this->moodEntries()->avg('mood_level') ?? 0;
    }

    /**
     * Get the user's timezone preference.
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Convert a time string to the user's timezone for display.
     */
    public function convertTimeToUserTimezone(string $time, string $date = null): string
    {
        $date = $date ?? now()->toDateString();
        $datetime = $date . ' ' . $time;
        
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i', $datetime, 'UTC')
            ->setTimezone($this->getTimezone())
            ->format('H:i');
    }
}
