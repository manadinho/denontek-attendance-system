<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Standard;

class HomeController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $standardQuery = Standard::withCount('students')->where('school_id', $school_id);

        if(userType() == 'teacher') {
            $standardQuery->whereHas('teachers', function($query) {
                $query->where('user_id', user()->id);
            });
        }

        $standards = $standardQuery->get();
        return view('dashboard', ['standards' => $standards]);
    }

    public function schools()
    {
        $schools = user()->load('schools')->schools;
        return view('schools', ['schools' => $schools]);
    }

    public function selectSchool($school_id)
    {
        $schools = user()->load('schools')->schools;
        
        if(!$schools->contains('id', $school_id)) {
            return redirect()->back();
        }

        session(['school_id' => $school_id]);
        return redirect()->route('dashboard');
    }
}
