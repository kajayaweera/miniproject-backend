<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    function index(){

        return User::all();
    }

    function register(Request $request ){
        $request->validate([
            'name'=> 'required',
            'email'=> 'required|email|unique:users',
            'contact_number'=> 'required',
            'address'=> 'required',
            'password'=> 'required'
        ]);
    }
}
