<?php

namespace App\Http\Controllers;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ChildProfileController extends Controller
{
    public function index()
    {
        $childProfiles = ChildProfile::all();
        return response()->json($childProfiles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'name' => 'required|string|max:255',
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'age' => 'required|integer',
            'mood' => 'nullable|string|max:255',
            'behavioural_overview' => 'nullable|string',
            'learning_progress' => 'nullable|string'
        ]);

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validated['profile_pic'] = URL::to('images/' . $imageName);
        }

        $childProfile = ChildProfile::create($validated);
        return response()->json($childProfile, 201);
    }

    public function show(ChildProfile $childProfile)
    {
        return response()->json($childProfile);
    }

    public function update(Request $request, ChildProfile $childProfile)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'profile_pic' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'age' => 'sometimes|required|integer',
            'mood' => 'nullable|string|max:255',
            'behavioural_overview' => 'nullable|string',
            'learning_progress' => 'nullable|string'
        ]);

        if ($request->hasFile('profile_pic')) {
            // Extract old image path and delete if exists
            if ($childProfile->profile_pic) {
                $oldImagePath = parse_url($childProfile->profile_pic, PHP_URL_PATH);
                $oldImagePath = public_path(ltrim($oldImagePath, '/'));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Save new image
            $image = $request->file('profile_pic');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validated['profile_pic'] = URL::to('images/' . $imageName);
        }

        $childProfile->update($validated);
        return response()->json($childProfile);
    }

    public function destroy(ChildProfile $childProfile)
    {
        $childProfile->delete();
        return response()->json(null, 204);
    }

    public function getChildProfile(User $user){
        $child = ChildProfile::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json($child);
    }
}
