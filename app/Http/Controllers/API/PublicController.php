<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Services\RedisService;

class PublicController extends Controller
{
    public function updateIsOnWhatsapp($contact, $status)
    {
        Student::where('guardian_contact', $contact)->update([
            'is_on_whatsapp' => $status == 1
        ]);

        $students = Student::where('guardian_contact', $contact)->get(['id', 'is_on_whatsapp', 'rfid', 'name', 'guardian_contact']);

        foreach ($students as $student) {
            app(RedisService::class)->upsertStudent($student);
        }

        info("Updated is_on_whatsapp to " . ($status == 1 ? 'true' : 'false') . " for contact: $contact");

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
        ]);
    }

    public function updateRedisCache()
    {
        app(RedisService::class)->rebuildAll();
    }
}
