<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MoodEntry;
use Laravel\Sanctum\Sanctum;

class MoodEntryApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_mood_entries()
    {
        $response = $this->getJson('/api/mood-entries');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $moodData = [
            'mood_level' => 4,
            'notes' => 'Feeling great today!',
            'activities' => ['work', 'exercise'],
            'entry_date' => now()->toDateString(),
            'entry_time' => '14:30'
        ];

        $response = $this->postJson('/api/mood-entries', $moodData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'mood_level',
                    'notes',
                    'activities',
                    'entry_date',
                    'entry_time',
                    'user_id',
                    'created_at',
                    'updated_at'
                ],
                'meta' => [
                    'mood_description',
                    'mood_emoji'
                ]
            ]);

        $this->assertDatabaseHas('mood_entries', [
            'user_id' => $this->user->id,
            'mood_level' => 4,
            'notes' => 'Feeling great today!',
            'entry_date' => now()->toDateString()
        ]);
    }

    /** @test */
    public function mood_entry_requires_valid_mood_level()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/mood-entries', [
            'mood_level' => 6, // Invalid - must be 1-5
            'notes' => 'Test'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mood_level']);
    }

    /** @test */
    public function user_can_get_their_mood_entries()
    {
        Sanctum::actingAs($this->user);

        // Create entries for this user
        MoodEntry::factory()->count(3)->create(['user_id' => $this->user->id]);

        // Create entries for other user (should not appear)
        MoodEntry::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->getJson('/api/mood-entries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'mood_level',
                            'entry_date',
                            'user_id'
                        ]
                    ],
                    'current_page',
                    'total'
                ],
                'meta' => [
                    'total_entries',
                    'average_mood',
                    'latest_entry'
                ]
            ]);

        // Should only return 3 entries (user's entries)
        $this->assertEquals(3, $response->json('data.total'));

        // All entries should belong to the authenticated user
        foreach ($response->json('data.data') as $entry) {
            $this->assertEquals($this->user->id, $entry['user_id']);
        }
    }

    /** @test */
    public function user_can_filter_mood_entries_by_date()
    {
        Sanctum::actingAs($this->user);

        MoodEntry::factory()->create([
            'user_id' => $this->user->id,
            'entry_date' => '2025-06-01'
        ]);

        MoodEntry::factory()->create([
            'user_id' => $this->user->id,
            'entry_date' => '2025-06-15'
        ]);

        $response = $this->getJson('/api/mood-entries?start_date=2025-06-10&end_date=2025-06-20');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total'));
    }

    /** @test */
    public function user_can_view_specific_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $moodEntry = MoodEntry::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/mood-entries/{$moodEntry->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $moodEntry->id,
                    'mood_level' => $moodEntry->mood_level,
                    'user_id' => $this->user->id
                ]
            ]);
    }

    /** @test */
    public function user_cannot_view_other_users_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $otherUserEntry = MoodEntry::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->getJson("/api/mood-entries/{$otherUserEntry->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized access to mood entry'
            ]);
    }

    /** @test */
    public function user_can_update_their_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $moodEntry = MoodEntry::factory()->create([
            'user_id' => $this->user->id,
            'mood_level' => 3,
            'notes' => 'Original note'
        ]);

        $updateData = [
            'mood_level' => 5,
            'notes' => 'Updated note',
            'activities' => ['celebration']
        ];

        $response = $this->putJson("/api/mood-entries/{$moodEntry->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Mood entry updated successfully'
            ]);

        $this->assertDatabaseHas('mood_entries', [
            'id' => $moodEntry->id,
            'mood_level' => 5,
            'notes' => 'Updated note'
        ]);
    }

    /** @test */
    public function user_cannot_update_other_users_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $otherUserEntry = MoodEntry::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->putJson("/api/mood-entries/{$otherUserEntry->id}", [
            'mood_level' => 5
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_their_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $moodEntry = MoodEntry::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/mood-entries/{$moodEntry->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Mood entry deleted successfully'
            ]);

        $this->assertDatabaseMissing('mood_entries', ['id' => $moodEntry->id]);
    }

    /** @test */
    public function user_cannot_delete_other_users_mood_entry()
    {
        Sanctum::actingAs($this->user);

        $otherUserEntry = MoodEntry::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->deleteJson("/api/mood-entries/{$otherUserEntry->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('mood_entries', ['id' => $otherUserEntry->id]);
    }

    /** @test */
    public function user_can_get_mood_statistics()
    {
        Sanctum::actingAs($this->user);

        // Create various mood entries
        MoodEntry::factory()->create(['user_id' => $this->user->id, 'mood_level' => 1]);
        MoodEntry::factory()->create(['user_id' => $this->user->id, 'mood_level' => 3]);
        MoodEntry::factory()->create(['user_id' => $this->user->id, 'mood_level' => 5]);

        $response = $this->getJson('/api/mood-entries-stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_entries',
                    'average_mood',
                    'latest_entry',
                    'this_month' => [
                        'count',
                        'average'
                    ],
                    'mood_distribution'
                ]
            ]);

        $this->assertEquals(3, $response->json('data.total_entries'));
        $this->assertEquals(3.0, $response->json('data.average_mood')); // (1+3+5)/3
    }

    /** @test */
    public function mood_entry_creates_with_defaults_when_date_time_not_provided()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/mood-entries', [
            'mood_level' => 4,
            'notes' => 'Test without date/time'
        ]);

        $response->assertStatus(201);

        $entry = MoodEntry::latest()->first();
        $this->assertEquals(now()->toDateString(), $entry->entry_date->format('Y-m-d'));
        $this->assertNotNull($entry->entry_time);
    }

    /** @test */
    public function user_can_create_multiple_mood_entries_for_same_date()
    {
        Sanctum::actingAs($this->user);

        // Create first entry for today
        $response1 = $this->postJson('/api/mood-entries', [
            'mood_level' => 3,
            'entry_date' => '2025-06-01',
            'entry_time' => '09:00',
            'notes' => 'Morning mood'
        ]);

        $response1->assertStatus(201);

        // Create second entry for the same date
        $response2 = $this->postJson('/api/mood-entries', [
            'mood_level' => 5,
            'entry_date' => '2025-06-01',
            'entry_time' => '18:00',
            'notes' => 'Evening mood'
        ]);

        $response2->assertStatus(201);

        // Both entries should exist in database
        $this->assertDatabaseHas('mood_entries', [
            'user_id' => $this->user->id,
            'mood_level' => 3,
            'entry_date' => '2025-06-01',
            'notes' => 'Morning mood'
        ]);

        $this->assertDatabaseHas('mood_entries', [
            'user_id' => $this->user->id,
            'mood_level' => 5,
            'entry_date' => '2025-06-01',
            'notes' => 'Evening mood'
        ]);

        // Should have 2 entries for this user
        $this->assertEquals(2, $this->user->moodEntries()->count());
    }
}
