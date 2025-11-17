<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salaries = Salary::with('user')->get();
        return response()->json($salaries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'salary_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'over_time' => 'required|numeric|min:0',
            'fuel_allowence' => 'required|numeric|min:0',
            'net_sallary' => 'required|numeric|min:0'
        ]);

        $salary = Salary::create($validated);
        return response()->json($salary, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Salary $salary)
    {
        return response()->json($salary);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'salary_date' => 'sometimes|required|date',
            'basic_salary' => 'sometimes|required|numeric|min:0',
            'over_time' => 'sometimes|required|numeric|min:0',
            'fuel_allowence' => 'sometimes|required|numeric|min:0',
            'net_sallary' => 'sometimes|required|numeric|min:0'
        ]);

        $salary->update($validated);
        return response()->json($salary);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salary $salary)
    {
        $salary->delete();
        return response()->json(null, 204);
    }

    public function getTeacherSalary(User $user){

        $sallary = Salary::where('user_id' , $user->id)->get();

        return $sallary;

    }
}
