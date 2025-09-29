<?php
// app/Services/AttendanceMessageRenderer.php
namespace App\Services;

use App\Models\AttendanceMessageTemplate;
use Illuminate\Support\Str;

class AttendanceMessageRenderer
{
    // Keep allowed placeholders centralized
    public const ALLOWED = [
        'student_name','father_name','roll_no','class_name','date_time','school_name'
    ];

    public function render(AttendanceMessageTemplate $tpl, array $data): string
    {
        $map = [];
        foreach (self::ALLOWED as $key) {
            $map['{'.$key.'}'] = (string) data_get($data, $key, '');
        }
        // basic replacement; for fancier rules use Blade::render or Mustache
        return strtr($tpl->body, $map);
    }
}
