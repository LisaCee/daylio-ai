<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;
use Carbon\Carbon;

class MoodEntryTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_mood_entry()
    {
        $user = User::factory()->create();

        $moodEntry = MoodEntry::factory()->create([
            'user_id' => $user->id,
            'mood_level' => 4,
            'entry_date' => '2024-01-15',
            'notes' => 'Had a great day!',
            'activities' => ['exercise', 'social_time']
        ]);

        $this->assertDatabaseHas('mood_entries', [
            'user_id' => $user->id,
            'mood_level' => 4,
        ]);

        // Check the actual model values
        $this->assertEquals('2024-01-15', $moodEntry->entry_date->format('Y-m-d'));
        $this->assertEquals('Had a great day!', $moodEntry->notes);
        $this->assertEquals(['exercise', 'social_time'], $moodEntry->activities);
    }

    /** @test */
    public function it_returns_correct_mood_descriptions()
    {
        $testCases = [
            1 => 'Very Bad',
            2 => 'Bad',
            3 => 'Neutral',
            4 => 'Good',
            5 => 'Very Good'
        ];

        foreach ($testCases as $level => $expectedDescription) {
            $moodEntry = MoodEntry::factory()->create(['mood_level' => $level]);
            $this->assertEquals($expectedDescription, $moodEntry->mood_description);
        }
    }

    /** @test */
    public function it_returns_correct_mood_emojis()
    {
        $testCases = [
            1 => '😞',
            2 => '😔',
            3 => '😐',
            4 => '😊',
            5 => '😁'
        ];

        foreach ($testCases as $level => $expectedEmoji) {
            $moodEntry = MoodEntry::factory()->create(['mood_level' => $level]);
            $this->assertEquals($expectedEmoji, $moodEntry->mood_emoji);
        }
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $moodEntry = MoodEntry::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $moodEntry->user);
        $this->assertEquals($user->id, $moodEntry->user->id);
    }

    /** @test */
    public function it_casts_activities_as_array()
    {
        $activities = ['exercise', 'work', 'social_time'];
        $moodEntry = MoodEntry::factory()->create(['activities' => $activities]);

        $this->assertIsArray($moodEntry->activities);
        $this->assertEquals($activities, $moodEntry->activities);
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $user = User::factory()->create();

        // Create entries across different dates
        MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => '2024-01-01'
        ]);
        MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => '2024-01-15'
        ]);
        MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => '2024-02-01'
        ]);

        $entries = MoodEntry::dateRange('2024-01-01', '2024-01-31')->get();

        $this->assertCount(2, $entries);
    }

    /** @test */
    public function it_can_filter_current_month()
    {
        $user = User::factory()->create();

        // Create entry for current month
        MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => Carbon::now()->format('Y-m-d')
        ]);

        // Create entry for last month
        MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => Carbon::now()->subMonth()->format('Y-m-d')
        ]);

        $currentMonthEntries = MoodEntry::currentMonth()->get();

        $this->assertCount(1, $currentMonthEntries);
    }

    /** @test */
    public function it_handles_invalid_mood_level_gracefully()
    {
        $moodEntry = MoodEntry::factory()->create(['mood_level' => 99]);

        $this->assertEquals('Unknown', $moodEntry->mood_description);
        $this->assertEquals('❓', $moodEntry->mood_emoji);
    }
}
