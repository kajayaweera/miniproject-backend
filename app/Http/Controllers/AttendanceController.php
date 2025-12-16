<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
    */
    public function index(): JsonResponse
    {
        $attendances = Attendance::orderBy('date', 'desc')->get();
        
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
            'date' => 'required|date|unique:attendances,date',
            'attendance' => 'required|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent',
        ]);

        // Validate that all user_ids have role == teacher
        $userIds = collect($validated['attendance'])->pluck('user_id')->unique();
        $nonTeachers = User::whereIn('id', $userIds)->where('role', '!=', 'teacher')->count();
        
        if ($nonTeachers > 0) {
            return response()->json([
                'success' => false,
                'message' => 'All users must have teacher role',
            ], 422);
        }
        
        // Transform the attendance data to store user_id as key
        $attendanceData = [];
        foreach ($validated['attendance'] as $record) {
            $attendanceData[$record['user_id']] = $record['status'];
        }
        
        $attendance = Attendance::create([
            'date' => $validated['date'],
            'attendance' => $attendanceData,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param Attendance $attendance
     * @return JsonResponse
    */
    public function show(Attendance $attendance): JsonResponse
    {
        // Transform attendance data to include teacher details
        $attendanceData = [];
        
        foreach ($attendance->attendance as $userId => $status) {
            $teacher = User::find($userId);
            $attendanceData[] = [
                'user_id' => $userId,
                'teacher_name' => $teacher ? $teacher->name : 'Unknown',
                'status' => $status,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date->format('Y-m-d'),
                'attendance' => $attendanceData,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param Attendance $attendance
     * @return JsonResponse
    */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date|unique:attendances,date,' . $attendance->id,
            'attendance' => 'sometimes|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent',
        ]);

        // Validate that all user_ids have role == teacher if attendance is being updated
        if (isset($validated['attendance'])) {
            $userIds = collect($validated['attendance'])->pluck('user_id')->unique();
            $nonTeachers = User::whereIn('id', $userIds)->where('role', '!=', 'teacher')->count();
            
            if ($nonTeachers > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'All users must have teacher role',
                ], 422);
            }
            
            // Transform the attendance data
            $attendanceData = [];
            foreach ($validated['attendance'] as $record) {
                $attendanceData[$record['user_id']] = $record['status'];
            }
            $validated['attendance'] = $attendanceData;
        }
        
        $attendance->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param Attendance $attendance
     * @return JsonResponse
    */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully',
        ]);
    }
}
