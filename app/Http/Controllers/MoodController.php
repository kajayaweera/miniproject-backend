<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $moods = Mood::orderBy('date', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $moods
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'mood' => 'required|array|min:1',
            'mood.*.child_profile_id' => 'required|exists:child_profiles,id',
            'mood.*.mood' => 'required|string|in:happy,sad,angry,excited,calm,anxious,frustrated,content,tired,energetic',
        ]);
        
        $mood = Mood::create([
            'date' => $validated['date'],
            'mood' => $validated['mood']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Mood record created successfully',
            'data' => $mood
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Mood $mood): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $mood
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mood $mood): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'mood' => 'sometimes|array|min:1',
            'mood.*.child_profile_id' => 'required|exists:child_profiles,id',
            'mood.*.mood' => 'required|string|in:happy,sad,angry,excited,calm,anxious,frustrated,content,tired,energetic',
        ]);
        
        $mood->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Mood record updated successfully',
            'data' => $mood
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mood $mood): JsonResponse
    {
        $mood->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Mood record deleted successfully'
        ], 200);
    }

    /**
     * Get the mood of a specific child for today's date using user_id.
     */
    public function getTodayMoodByChild($userId): JsonResponse
    {
        $childProfile = \App\Models\ChildProfile::where('user_id', $userId)->latest()->first();
        
        if (!$childProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Child profile not found for this user'
            ], 404);
        }
        
        $today = now()->format('Y-m-d');
        
        $moodRecord = Mood::whereDate('date', $today)->first();
        
        if (!$moodRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No mood records found for today'
            ], 404);
        }
        
        $childMood = collect($moodRecord->mood)->firstWhere('child_profile_id', $childProfile->id);
        
        if (!$childMood) {
            return response()->json([
                'success' => false,
                'message' => 'No mood found for this child today'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'date' => $moodRecord->date,
                'child_profile_id' => $childMood['child_profile_id'],
                'mood' => $childMood['mood']
            ]
        ], 200);
    }

    /**
     * Get mood statistics for a specific child for bar chart.
     */
    public function getMoodStatistics($userId): JsonResponse
    {
        $childProfile = \App\Models\ChildProfile::where('user_id', $userId)->latest()->first();
        
        if (!$childProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Child profile not found for this user'
            ], 404);
        }
        
        $moodRecords = Mood::all();
        
        $moodCounts = [
            'happy' => 0,
            'sad' => 0,
            'angry' => 0,
            'excited' => 0,
            'calm' => 0,
            'anxious' => 0,
            'frustrated' => 0,
            'content' => 0,
            'tired' => 0,
            'energetic' => 0
        ];
        
        foreach ($moodRecords as $record) {
            $childMoods = collect($record->mood)->where('child_profile_id', $childProfile->id);
            
            foreach ($childMoods as $moodEntry) {
                if (isset($moodEntry['mood']) && isset($moodCounts[$moodEntry['mood']])) {
                    $moodCounts[$moodEntry['mood']]++;
                }
            }
        }
        
        $chartData = [
            'labels' => array_keys($moodCounts),
            'data' => array_values($moodCounts)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $chartData
        ], 200);
    }
}
