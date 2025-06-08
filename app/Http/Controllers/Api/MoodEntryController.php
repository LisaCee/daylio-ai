<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoodEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MoodEntryController extends Controller
{
    /**
     * Display a listing of the user's mood entries.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->moodEntries()->orderBy('entry_date', 'desc')->orderBy('entry_time', 'desc');

        // Optional filtering
        if ($request->has('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }

        if ($request->has('mood_level')) {
            $query->where('mood_level', $request->mood_level);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $entries = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $entries,
            'meta' => [
                'total_entries' => $user->moodEntries()->count(),
                'average_mood' => $user->averageMoodLevel(),
                'latest_entry' => $user->latestMoodEntry()?->only(['mood_level', 'entry_date', 'mood_description', 'mood_emoji'])
            ]
        ]);
    }

    /**
     * Store a newly created mood entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood_level' => 'required|integer|between:1,5',
            'entry_date' => 'nullable|date|before_or_equal:today',
            'entry_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'activities' => 'nullable|array',
            'activities.*' => 'string|max:100',
        ]);

        // Set defaults
        $validated['user_id'] = $request->user()->id;
        $validated['entry_date'] = $validated['entry_date'] ?? now()->toDateString();
        $validated['entry_time'] = $validated['entry_time'] ?? now($request->user()->getTimezone())->format('H:i');

        $moodEntry = MoodEntry::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mood entry created successfully',
            'data' => $moodEntry->load('user:id,name'),
            'meta' => [
                'mood_description' => $moodEntry->mood_description,
                'mood_emoji' => $moodEntry->mood_emoji
            ]
        ], 201);
    }

    /**
     * Display the specified mood entry.
     */
    public function show(Request $request, MoodEntry $moodEntry): JsonResponse
    {
        // Ensure user can only view their own entries
        if ($moodEntry->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to mood entry'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $moodEntry->load('user:id,name'),
            'meta' => [
                'mood_description' => $moodEntry->mood_description,
                'mood_emoji' => $moodEntry->mood_emoji
            ]
        ]);
    }

    /**
     * Update the specified mood entry.
     */
    public function update(Request $request, MoodEntry $moodEntry): JsonResponse
    {
        // Ensure user can only update their own entries
        if ($moodEntry->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to mood entry'
            ], 403);
        }

        $validated = $request->validate([
            'mood_level' => 'sometimes|integer|between:1,5',
            'entry_date' => 'sometimes|date|before_or_equal:today',
            'entry_time' => 'sometimes|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'activities' => 'nullable|array',
            'activities.*' => 'string|max:100',
        ]);

        $moodEntry->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mood entry updated successfully',
            'data' => $moodEntry->fresh()->load('user:id,name'),
            'meta' => [
                'mood_description' => $moodEntry->mood_description,
                'mood_emoji' => $moodEntry->mood_emoji
            ]
        ]);
    }

    /**
     * Remove the specified mood entry.
     */
    public function destroy(Request $request, MoodEntry $moodEntry): JsonResponse
    {
        // Ensure user can only delete their own entries
        if ($moodEntry->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to mood entry'
            ], 403);
        }

        $moodEntry->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mood entry deleted successfully'
        ]);
    }

    /**
     * Get mood statistics for the user.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_entries' => $user->moodEntries()->count(),
            'average_mood' => $user->averageMoodLevel(),
            'latest_entry' => $user->latestMoodEntry(),
            'this_month' => [
                'count' => $user->moodEntries()->currentMonth()->count(),
                'average' => $user->moodEntries()->currentMonth()->avg('mood_level') ?? 0,
            ],
            'mood_distribution' => $user->moodEntries()
                ->selectRaw('mood_level, COUNT(*) as count')
                ->groupBy('mood_level')
                ->orderBy('mood_level')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->mood_level => [
                        'count' => $item->count,
                        'description' => MoodEntry::getMoodDescription($item->mood_level),
                        'emoji' => MoodEntry::getMoodEmoji($item->mood_level)
                    ]];
                })
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
