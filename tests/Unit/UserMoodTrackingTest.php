<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\MoodEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;
use Carbon\Carbon;

class UserMoodTrackingTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_many_mood_entries()
    {
        $user = User::factory()->create();
        $moodEntries = MoodEntry::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->moodEntries);
        $this->assertInstanceOf(MoodEntry::class, $user->moodEntries->first());
    }

    /** @test */
    public function it_can_get_latest_mood_entry()
    {
        $user = User::factory()->create();

        // Create older entry
        $oldEntry = MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => '2024-01-01',
            'mood_level' => 3
        ]);

        // Create newer entry
        $newEntry = MoodEntry::factory()->create([
            'user_id' => $user->id,
            'entry_date' => '2024-01-15',
            'mood_level' => 5
        ]);

        $latestEntry = $user->latestMoodEntry();

        $this->assertEquals($newEntry->id, $latestEntry->id);
        $this->assertEquals(5, $latestEntry->mood_level);
    }

    /** @test */
    public function it_returns_null_when_no_mood_entries_exist()
    {
        $user = User::factory()->create();

        $this->assertNull($user->latestMoodEntry());
    }

    /** @test */
    public function it_calculates_average_mood_level()
    {
        $user = User::factory()->create();

        // Create mood entries with levels: 1, 2, 3, 4, 5 (average = 3.0)
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 1]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 2]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 3]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 4]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 5]);

        $this->assertEquals(3.0, $user->averageMoodLevel());
    }

    /** @test */
    public function it_returns_zero_average_when_no_mood_entries()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->averageMoodLevel());
    }

    /** @test */
    public function it_calculates_average_with_decimal_precision()
    {
        $user = User::factory()->create();

        // Create mood entries with levels: 4, 4, 5 (average = 4.33... = 4.3 with 1 decimal)
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 4]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 4]);
        MoodEntry::factory()->create(['user_id' => $user->id, 'mood_level' => 5]);

        $average = $user->averageMoodLevel();
        $this->assertEqualsWithDelta(4.3, round($average, 1), 0.1);
    }

    /** @test */
    public function it_only_counts_user_specific_mood_entries()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create entries for user1
        MoodEntry::factory()->create(['user_id' => $user1->id, 'mood_level' => 5]);
        MoodEntry::factory()->create(['user_id' => $user1->id, 'mood_level' => 5]);

        // Create entries for user2
        MoodEntry::factory()->create(['user_id' => $user2->id, 'mood_level' => 1]);
        MoodEntry::factory()->create(['user_id' => $user2->id, 'mood_level' => 1]);

        $this->assertEquals(5.0, $user1->averageMoodLevel());
        $this->assertEquals(1.0, $user2->averageMoodLevel());
        $this->assertCount(2, $user1->moodEntries);
        $this->assertCount(2, $user2->moodEntries);
    }

    /** @test */
    public function mood_entries_are_deleted_when_user_is_deleted()
    {
        $user = User::factory()->create();
        $moodEntry = MoodEntry::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('mood_entries', ['id' => $moodEntry->id]);

        $user->delete();

        $this->assertDatabaseMissing('mood_entries', ['id' => $moodEntry->id]);
    }
}
