<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MoodEntry;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class TimezoneTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_register_with_timezone()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'America/New_York'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful',
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'timezone' => 'America/New_York'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'timezone' => 'America/New_York'
        ]);
    }

    /** @test */
    public function user_can_register_without_timezone_and_gets_default()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'timezone' => 'UTC' // Should default to UTC
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'timezone' => 'UTC'
        ]);
    }

    /** @test */
    public function user_can_register_with_timezone_header()
    {
        $response = $this->withHeaders([
            'X-Timezone' => 'Europe/London'
        ])->postJson('/api/auth/register', [
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'timezone' => 'Europe/London'
                ]
            ]);
    }

    /** @test */
    public function explicit_timezone_overrides_header()
    {
        $response = $this->withHeaders([
            'X-Timezone' => 'Europe/London'
        ])->postJson('/api/auth/register', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'Asia/Tokyo' // This should override the header
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'timezone' => 'Asia/Tokyo'
                ]
            ]);
    }

    /** @test */
    public function login_returns_user_timezone()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'timezone' => 'Australia/Sydney'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'timezone' => 'Australia/Sydney'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_timezone_in_profile()
    {
        $user = User::factory()->create([
            'timezone' => 'UTC'
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/profile', [
            'timezone' => 'America/Los_Angeles'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'timezone' => 'America/Los_Angeles'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'timezone' => 'America/Los_Angeles'
        ]);
    }

    /** @test */
    public function user_profile_endpoint_returns_timezone()
    {
        $user = User::factory()->create([
            'timezone' => 'Pacific/Auckland'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'timezone' => 'Pacific/Auckland'
                ]
            ]);
    }

    /** @test */
    public function mood_entry_uses_user_timezone_for_default_time()
    {
        // Create user with New York timezone
        $user = User::factory()->create([
            'timezone' => 'America/New_York'
        ]);

        Sanctum::actingAs($user);

        // Mock the current time to be a specific UTC time
        Carbon::setTestNow(Carbon::parse('2024-01-15 20:00:00 UTC')); // 8 PM UTC

        $response = $this->postJson('/api/mood-entries', [
            'mood_level' => 4,
            'notes' => 'Test entry'
        ]);

        $response->assertStatus(201);

        // The mood entry should have the time in the user's timezone
        // 8 PM UTC = 3 PM Eastern Time (assuming standard time)
        $moodEntry = MoodEntry::latest()->first();
        
        // The entry_time should be in user's timezone format
        $expectedTime = Carbon::parse('2024-01-15 20:00:00 UTC')
            ->setTimezone('America/New_York')
            ->format('H:i');

        // Since entry_time is cast as datetime, we need to format it
        $actualTime = $moodEntry->entry_time->format('H:i');
        
        $this->assertEquals($expectedTime, $actualTime);

        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function user_model_get_timezone_method_works()
    {
        $user = User::factory()->make([
            'timezone' => 'Europe/Paris'
        ]);

        $this->assertEquals('Europe/Paris', $user->getTimezone());
    }

    /** @test */
    public function user_model_get_timezone_returns_default_when_null()
    {
        $user = User::factory()->make([
            'timezone' => null
        ]);

        $this->assertEquals(config('app.timezone', 'UTC'), $user->getTimezone());
    }

    /** @test */
    public function timezone_validation_rejects_invalid_timezones()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'Invalid/Timezone'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    }

    /** @test */
    public function timezone_conversion_method_works()
    {
        $user = User::factory()->make([
            'timezone' => 'America/New_York'
        ]);

        // Test converting UTC time to user's timezone
        $utcTime = '15:00'; // 3 PM UTC
        $date = '2024-06-15'; // Summer time

        $convertedTime = $user->convertTimeToUserTimezone($utcTime, $date);

        // 3 PM UTC should be 11 AM EDT in summer
        $this->assertEquals('11:00', $convertedTime);
    }

    /** @test */
    public function timezone_conversion_uses_current_date_when_not_provided()
    {
        $user = User::factory()->make([
            'timezone' => 'Europe/London'
        ]);

        $utcTime = '12:00';
        
        $convertedTime = $user->convertTimeToUserTimezone($utcTime);
        
        // Should not throw an error and should return a valid time
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $convertedTime);
    }
}
