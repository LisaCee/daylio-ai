<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MoodEntry>
 */
class MoodEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $moodLevel = $this->faker->numberBetween(1, 5);

        // Define activities based on mood level for more realistic data
        $activities = $this->getActivitiesForMoodLevel($moodLevel);

        return [
            'user_id' => User::factory(),
            'mood_level' => $moodLevel,
            'entry_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional(0.7)->paragraph(2), // 70% chance of notes
            'activities' => $this->faker->randomElements($activities, $this->faker->numberBetween(1, 4)),
            'entry_time' => $this->faker->time('H:i'),
        ];
    }

    /**
     * Get realistic activities based on mood level.
     */
    private function getActivitiesForMoodLevel(int $moodLevel): array
    {
        $allActivities = [
            // Positive activities (more likely with higher moods)
            'exercise',
            'social_time',
            'hobbies',
            'work_productivity',
            'good_sleep',
            'healthy_eating',
            'outdoor_time',
            'meditation',
            'reading',
            'music',

            // Neutral activities
            'work',
            'chores',
            'tv_movies',
            'cooking',
            'shopping',
            'commuting',

            // Activities that might correlate with lower moods
            'stressed',
            'tired',
            'sick',
            'lonely',
            'anxious',
            'overwhelmed'
        ];

        // Adjust activity likelihood based on mood
        if ($moodLevel >= 4) {
            // Good/Very Good moods - more positive activities
            return array_slice($allActivities, 0, 10);
        } elseif ($moodLevel <= 2) {
            // Bad/Very Bad moods - mix with more challenging activities
            return array_merge(
                array_slice($allActivities, 10, 6), // neutral
                array_slice($allActivities, 16, 6)  // challenging
            );
        } else {
            // Neutral mood - balanced mix
            return array_slice($allActivities, 0, 16);
        }
    }

    /**
     * Create entries for the last 30 days.
     */
    public function lastThirtyDays()
    {
        return $this->state(function (array $attributes) {
            return [
                'entry_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            ];
        });
    }

    /**
     * Create a very good mood entry.
     */
    public function veryGood()
    {
        return $this->state(function (array $attributes) {
            return [
                'mood_level' => 5,
                'activities' => ['exercise', 'social_time', 'good_sleep', 'hobbies'],
                'notes' => 'Had an amazing day! Everything went perfectly.',
            ];
        });
    }

    /**
     * Create a very bad mood entry.
     */
    public function veryBad()
    {
        return $this->state(function (array $attributes) {
            return [
                'mood_level' => 1,
                'activities' => ['stressed', 'tired', 'overwhelmed'],
                'notes' => 'Really tough day. Everything felt overwhelming.',
            ];
        });
    }
}
