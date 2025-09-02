<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use App\Http\Controllers\Controller;

class PublicController extends Controller
{
    public function updateIsOnWhatsapp($contact, $status)
    {
        Student::where('guardian_contact', $contact)->update([
            'is_on_whatsapp' => $status == 1
        ]);

        info("Updated is_on_whatsapp to " . ($status == 1 ? 'true' : 'false') . " for contact: $contact");

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
        ]);
    }
}
