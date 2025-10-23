<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id');
        $teachers = User::whereHas('schools', function($q) use ($schoolIds){
            $q->whereIn('schools.id', $schoolIds)->where('role','teacher');
        })->orderBy('name')->get();
        return view('supervisor.users.index', compact('teachers'));
    }
}
