<?php

namespace App\Http\Controllers;

use App\Models\AdminAlert;
use App\Models\SchoolSetting;
use App\Services\AdminAlertService;
use Carbon\Carbon;

class AdminAlertController extends Controller
{
    public function index()
    {
        $alerts = AdminAlert::where('school_id', session('school_id'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $adminPhoneNumbers = SchoolSetting::where('school_id', session('school_id'))
            ->value('admin_phone_numbers');

        return view('admin-alerts.index', ['alerts' => $alerts, 'adminPhoneNumbers' => $adminPhoneNumbers ? json_decode($adminPhoneNumbers, true) : []]);
    }

    public function update()
    {
        $request = request();

        $request->validate([
            'id'             => 'required|exists:admin_alerts,id',
            'time'           => ['required'],
            'admin_contacts' => 'nullable|array',
            'active'         => 'nullable',
        ]);

        $result = app(AdminAlertService::class)->updateAdminAlert($request);

        if(!$result) {
            return redirect()->route('admin-alerts.index')->with('error', 'Something went wrong!');
        }
        return back()->with('status', 'Admin alert updated.');
    }

    public function sendAlerts()
    {
        $alerts = AdminAlert::where('active', true)
            ->whereTime('time', '>=', Carbon::now()->subMinutes(5)->format('H:i'))
            ->whereTime('time', '<=', Carbon::now()->addMinutes(5)->format('H:i'))
            ->get();

        foreach ($alerts as $alert) {
            app(AdminAlertService::class)->sendAdminAlert($alert);
        }

        return response()->json(['message' => 'Alerts processed.']);
    }
}