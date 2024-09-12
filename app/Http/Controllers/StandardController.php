<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\User;

class StandardController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $standards = Standard::withCount('students')->where('school_id', $school_id)->with('teachers', function($query) {$query->select('id', 'name');})->paginate(15);
        $teachers = User::where([['type', '=', 'teacher'], ['school_id', '=', $school_id]])->get(['id', 'name']);
        return view('standards.index', ['standards' => $standards, 'teachers' => $teachers]);
    }

    public function store(Request $request)
    {
        $id = $request->id ? $request->id : null;

        $validatedData = $request->validate([
            'name' => 'required',
            'teachers' => ['required', 'array'],
        ]);

        $standard = $id ? Standard::where('id', $id)->firstOrFail() : new Standard;

        if($id && $standard->school_id != session('school_id')) {
            return redirect()->route('standards.index')->with('error', 'Something went wrong!');
        }

        $standard->name = $validatedData['name'];
        $standard->school_id = session('school_id');

        $standard->save();

        $standard->teachers()->sync($validatedData['teachers']);

        $message = $id ? 'Standard updated successfully' : 'Standard created successfully';
        return redirect()->route('standards.index')->with('success', $message);
    }

    public function destroy($standard_id)
    {
        $standard = Standard::where('id', $standard_id)->firstOrFail();
        if($standard->school_id != session('school_id')) {
            return redirect()->route('standards.index')->with('error', 'Something went wrong!');
        }

        $standard->teachers()->detach();

        $standard->delete();

        return redirect()->back()->with('success', 'Standard deleted successfully');
    }
}
