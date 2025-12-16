<?php

namespace App\Http\Controllers;

use App\Models\ChildAttendance;
use App\Models\ChildProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChildAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
    */
    public function index(): JsonResponse
    {
        $attendances = ChildAttendance::orderBy('date', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @return JsonResponse
    */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:child_attendances,date',
            'attendance' => 'required|array',
            'attendance.*.child_profile_id' => 'required|exists:child_profiles,id',
            'attendance.*.status' => 'required|in:present,absent',
        ]);

        // Transform the attendance data to store child_profile_id as key
        $attendanceData = [];
        foreach ($validated['attendance'] as $record) {
            $attendanceData[$record['child_profile_id']] = $record['status'];
        }
        
        $attendance = ChildAttendance::create([
            'date' => $validated['date'],
            'attendance' => $attendanceData,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Child attendance recorded successfully',
            'data' => $attendance,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param ChildAttendance $childAttendance
     * @return JsonResponse
    */
    public function show(ChildAttendance $childAttendance): JsonResponse
    {
        // Transform attendance data to include child profile details
        $attendanceData = [];
        
        foreach ($childAttendance->attendance as $childProfileId => $status) {
            $childProfile = ChildProfile::find($childProfileId);
            $attendanceData[] = [
                'child_profile_id' => $childProfileId,
                'child_name' => $childProfile ? $childProfile->name : 'Unknown',
                'status' => $status,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $childAttendance->id,
                'date' => $childAttendance->date->format('Y-m-d'),
                'attendance' => $attendanceData,
                'created_at' => $childAttendance->created_at,
                'updated_at' => $childAttendance->updated_at,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param ChildAttendance $childAttendance
     * @return JsonResponse
    */
    public function update(Request $request, ChildAttendance $childAttendance): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date|unique:child_attendances,date,' . $childAttendance->id,
            'attendance' => 'sometimes|array',
            'attendance.*.child_profile_id' => 'required|exists:child_profiles,id',
            'attendance.*.status' => 'required|in:present,absent',
        ]);

        // Transform the attendance data if being updated
        if (isset($validated['attendance'])) {
            $attendanceData = [];
            foreach ($validated['attendance'] as $record) {
                $attendanceData[$record['child_profile_id']] = $record['status'];
            }
            $validated['attendance'] = $attendanceData;
        }
        
        $childAttendance->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Child attendance updated successfully',
            'data' => $childAttendance,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param ChildAttendance $childAttendance
     * @return JsonResponse
    */
    public function destroy(ChildAttendance $childAttendance): JsonResponse
    {
        $childAttendance->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Child attendance deleted successfully',
        ]);
    }

    /**
     * Get attendance statistics for a specific child using user_id.
     * 
     * @param int $userId
     * @return JsonResponse
    */
    public function getAttendanceStatistics($userId): JsonResponse
    {
        $childProfile = ChildProfile::where('user_id', $userId)->latest()->first();
        
        if (!$childProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Child profile not found for this user'
            ], 404);
        }
        
        $attendanceRecords = ChildAttendance::all();
        
        $presentCount = 0;
        $absentCount = 0;
        
        foreach ($attendanceRecords as $record) {
            if (isset($record->attendance[$childProfile->id])) {
                $status = $record->attendance[$childProfile->id];
                if ($status === 'present') {
                    $presentCount++;
                } elseif ($status === 'absent') {
                    $absentCount++;
                }
            }
        }
        
        $totalDays = $presentCount + $absentCount;
        $attendanceRate = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 2) : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'child_profile_id' => $childProfile->id,
                'total_days' => $totalDays,
                'present' => $presentCount,
                'absent' => $absentCount,
                'attendance_rate' => $attendanceRate,
            ]
        ], 200);
    }
}
