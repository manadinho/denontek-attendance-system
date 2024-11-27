<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');

        $employees = User::where('school_id', $school_id)->with('shifts')->get();

        $shifts = Shift::where('school_id', $school_id)->get();
        
        $timetables = Timetable::where('school_id', $school_id)->get();

        return view('employee-schedule.index', compact('employees', 'shifts', 'timetables'));
    }

    public function store() 
    {
        $data = request()->validate([
            'employees' => 'required|array',
            'shift_id' => 'required|exists:shifts,id',
            'from' => 'required|date',
            'to' => 'required|date|after:from'
        ]);

        // convert date to Y-m-d format
        $data['from'] = Carbon::createFromFormat('m/d/Y', $data['from'])->format('Y-m-d');
        $data['to'] = Carbon::createFromFormat('m/d/Y', $data['to'])->format('Y-m-d');

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid data']);
        }

        try {

            // check shift belongs to current school
            $this->checkShift($data['shift_id']);

            [$realEmployees, $realEmployeesCount, $rejectedEmployees, $rejectedEmployeesCount] = $this->getRealAndRejectedEmployees($data);

            $dataToInsert = [];
            foreach ($realEmployees as $user) {
                $dataToInsert[] = [
                    'user_id' => $user->id,
                    'shift_id' => $data['shift_id'],
                    'start_date' => $data['from'],
                    'end_date' => $data['to'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => auth()->id(),
                ];  
            }

            DB::table('shift_user')->insert($dataToInsert);

            $message = 'Shift assigned to ' . $realEmployeesCount . ' employees ';
            if ($rejectedEmployeesCount) {
                $message .= '. ' . $rejectedEmployeesCount . ' employees were rejected';
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    
    }

    public function destroy()
    {
        $data = request()->validate([
            'user_id' => 'required',
            'shift_id' => 'required'
        ]); 

        if(!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid data']);
        }

        try {
            $school_id = session('school_id');

            // check if shift belongs to current school
            $this->checkShift($data['shift_id']);

            // check if user belongs to current school
            $this->checkUser($data['user_id']);

            DB::table('shift_user')->where(['user_id' => $data['user_id'], 'shift_id' => $data['shift_id']])->delete();

            return response()->json(['success' => true, 'message' => 'Shift removed from user successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function checkShift($shiftId) 
    {
        $school_id = session('school_id');

        // shift belongs to current school
        $shift = Shift::where('school_id', $school_id)->find($shiftId);

        if (!$shift) {
            throw new \Exception('Invalid shift');
        }
    }

    private function checkUser($userId)
    {
        $school_id = session('school_id');

        $user = User::where('school_id', $school_id)->find($userId);

        if (!$user) {
            throw new \Exception('Invalid Employee');
        }
    }

    private function getRealAndRejectedEmployees($data)
    {
        $school_id = session('school_id');

        $realEmployees = [];
        $realEmployeesCount = 0;

        $rejectedEmployees = [];
        $rejectedEmployeesCount = 0;

         // check if shift is already assigned to user
        foreach ($data['employees'] as $userId) {
            $user = User::where('school_id',$school_id)->find($userId);
            if (!$user) {
                $rejectedEmployees[] = 'Unknown';
                $rejectedEmployeesCount++;
                continue;
            }

            $userShift = $user->shifts()->where('start_date', '<=', $data['from'])
                ->where('end_date', '>=', $data['to'])
                ->first();

            if ($userShift) {
                $rejectedEmployees[] = $user->name;
                $rejectedEmployeesCount++;
                continue;
            }

            $realEmployees[] = $user;
            $realEmployeesCount++;
        }

        return [$realEmployees, $realEmployeesCount, $rejectedEmployees, $rejectedEmployeesCount];
    }
}
