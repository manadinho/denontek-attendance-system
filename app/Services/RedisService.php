<?php

namespace App\Services;

use App\Models\Student;
use App\Models\School;
use Illuminate\Support\Facades\Redis;

class RedisService
{
    private const STUDENTS_KEY = 'students';
    private const SCHOOLS_KEY  = 'schools';

    private const MESSAGE_TEMPLATES_KEY = 'attendance_message_templates';
    // tune as needed
    private const STUDENT_DB_CHUNK = 5000; // rows per DB chunk
    private const SCHOOL_DB_CHUNK  = 1000; // rows per DB chunk

    /** Rebuild both caches via streaming */
    // public function rebuildAll(): array
    // {
    //     $schools = $this->rebuildSchools();
    //     $students = $this->rebuildStudents();

    //     return ['schools_cached' => $schools, 'students_cached' => $students];
    // }

    /** Stream-build students -> attend:students (field = rfid, value = JSON) */
    // public function rebuildStudents(): int
    // {
    //     $tmp = self::STUDENTS_KEY . ':tmp:' . Str::uuid();

    //     Student::query()
    //         ->where('is_on_whatsapp', 1)
    //         ->whereNotNull('rfid')
    //         ->select(['id', 'rfid', 'name', 'guardian_contact'])
    //         ->chunkById(self::STUDENT_DB_CHUNK, function ($chunk) use ($tmp) {
    //             Redis::pipeline(function ($pipe) use ($chunk, $tmp) {
    //                 foreach ($chunk as $s) {
    //                     $pipe->hset($tmp, $s->rfid, json_encode([
    //                         'id'               => $s->id,
    //                         'name'             => $s->name,
    //                         'guardian_contact' => $s->guardian_contact,
    //                     ], JSON_UNESCAPED_UNICODE));
    //                 }
    //             });
    //         });

    //     // Atomic swap (handle empty dataset safely)
    //     if (Redis::exists($tmp)) {
    //         Redis::rename($tmp, self::STUDENTS_KEY);
    //     } else {
    //         Redis::del(self::STUDENTS_KEY);
    //     }

    //     return (int) Redis::hlen(self::STUDENTS_KEY);
    // }

    /**
     * Stream-build schools -> attend:schools
     * field = device.mac_address (push_to_server), value = JSON(school row)
     *
     * Uses Device as the driving table for efficient chunking.
     */
    // public function rebuildSchools(): int
    // {
    //     $tmp = self::SCHOOLS_KEY . ':tmp:' . Str::uuid();

    //     Device::query()
    //         ->where('devices.type', 'push_to_server')
    //         ->join('schools', 'schools.id', '=', 'devices.school_id')
    //         ->leftJoin('school_settings', 'school_settings.school_id', '=', 'schools.id')
    //         ->orderBy('devices.id') // required for chunkById
    //         ->select([
    //             'devices.id as id',
    //             'devices.mac_address',
    //             'schools.id as school_id',
    //             'schools.name',
    //             'school_settings.checkin_start',
    //             'school_settings.checkin_end',
    //             'school_settings.checkout_start',
    //             'school_settings.checkout_end',
    //         ])
    //         ->chunkById(self::SCHOOL_DB_CHUNK, function ($chunk) use ($tmp) {
    //             Redis::pipeline(function ($pipe) use ($chunk, $tmp) {
    //                 foreach ($chunk as $row) {
    //                     $payload = [
    //                         'id'             => $row->school_id,
    //                         'name'           => $row->name,
    //                         'checkin_start'  => $row->checkin_start,
    //                         'checkin_end'    => $row->checkin_end,
    //                         'checkout_start' => $row->checkout_start,
    //                         'checkout_end'   => $row->checkout_end,
    //                     ];
    //                     $pipe->hset($tmp, implode('-', explode(':', $row->mac_address)), json_encode($payload, JSON_UNESCAPED_UNICODE));
    //                 }
    //             });
    //         }, 'id'); // id column for chunkById

    //     if (Redis::exists($tmp)) {
    //         Redis::rename($tmp, self::SCHOOLS_KEY);
    //     } else {
    //         Redis::del(self::SCHOOLS_KEY);
    //     }

    //     return (int) Redis::hlen(self::SCHOOLS_KEY);
    // }

    /* ----- Incremental upserts/deletes (same as before, safe for 100k) ----- */

    public static function upsertStudent(Student $s): void
    {
        // remove if off WhatsApp or RFID missing
        if (!$s->is_on_whatsapp || !$s->rfid) {
            self::callWhatsappServerEndpointToDeleteFromRedis(self::STUDENTS_KEY, $s->rfid);
            return;
        }

        self::callWhatsappServerEndpointToUpdateRedis(self::STUDENTS_KEY, $s->rfid, json_encode([
            'id'               => $s->id,
            'name'             => $s->name,
            'guardian_contact' => $s->guardian_contact,
            'guardian_name'    => $s->guardian_name,
            'standard_name'    => $s->standard->name,
        ], JSON_UNESCAPED_UNICODE));
    }

    public static function removeStudent(Student $s): void
    {
        if ($s->rfid) {
            Redis::hdel(self::STUDENTS_KEY, $s->rfid);
        }
    }

    public static function upsertSchool(string $schoolId): void
    {
        // $row = Device::query()
        //     ->where('schools.id', $schoolId)
        //     ->where('devices.type', 'push_to_server')
        //     ->join('schools', 'schools.id', '=', 'devices.school_id')
        //     ->leftJoin('school_settings', 'school_settings.school_id', '=', 'schools.id')
        //     ->select([
        //         'schools.id as school_id', 'schools.name',
        //         'school_settings.checkin_start', 'school_settings.checkin_end',
        //         'school_settings.checkout_start', 'school_settings.checkout_end',
        //         'school_settings.buffer_minutes', 'school_settings.week_off_days',
        //         'devices.mac_address',
        //     ])
        //     ->first();

        $school = School::where('id', $schoolId)
                    ->with('schoolSetting')->first();

        $payload = [
            'id'             => $schoolId,
            'name'           => $school->name,
            'checkin_start'  => $school->schoolSetting->checkin_start,
            'checkin_end'    => $school->schoolSetting->checkin_end,
            'checkout_start' => $school->schoolSetting->checkout_start,
            'checkout_end'   => $school->schoolSetting->checkout_end,
            'buffer_minutes' => $school->schoolSetting->buffer_minutes,
            'week_off_days'  => $school->schoolSetting->week_off_days
        ];

        self::callWhatsappServerEndpointToUpdateRedis(self::SCHOOLS_KEY, implode('-', explode(':', $school->channel_id)), json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public static function removeSchoolByMac(string $mac): void
    {
        Redis::hdel(self::SCHOOLS_KEY, $mac);
    }

    public static function upsertMessageTemplates($s, $channelId): void
    {

        self::callWhatsappServerEndpointToUpdateRedis(self::MESSAGE_TEMPLATES_KEY, $channelId, json_encode($s, JSON_UNESCAPED_UNICODE));
    }

    // public static function removeMessageTemplates(AttendanceMessageTemplate $s): void
    // {
    //     if ($s->rfid) {
    //         Redis::hdel(self::MESSAGE_TEMPLATES_KEY, $s->rfid);
    //     }
    // }

    private static function callWhatsappServerEndpointToUpdateRedis($hash, $key, $value)
    {
        info(env('NODE_SERVICE') . '/redis/set');
        $response = \Http::post(env('NODE_SERVICE') . '/redis/set', [
            'hash'  => $hash,
            'key'   => $key,
            'value' => $value,
        ]);

        info($response->body());

        if( $response->failed() ) {
            // log call failure
            info($response->failed());
            info('Failed to call WhatsApp server endpoint to update Redis cache');
        }
        info('Called WhatsApp server endpoint to update Redis cache');
    }

    private static function callWhatsappServerEndpointToDeleteFromRedis($hash, $key)
    {
        $response = \Http::post(env('NODE_SERVICE') . '/redis/hdel', [
            'hash' => $hash,
            'key'  => $key,
        ]);

        if( $response->failed() ) {
            info('Failed to call WhatsApp server endpoint to delete from Redis cache');
        }
        info('Called WhatsApp server endpoint to delete from Redis cache');
    }
}
