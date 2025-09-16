<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Device;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RedisService
{
    private const STUDENTS_KEY = 'students';
    private const SCHOOLS_KEY  = 'schools';
    // tune as needed
    private const STUDENT_DB_CHUNK = 5000; // rows per DB chunk
    private const SCHOOL_DB_CHUNK  = 1000; // rows per DB chunk

    /** Rebuild both caches via streaming */
    public function rebuildAll(): array
    {
        $schools = $this->rebuildSchools();
        $students = $this->rebuildStudents();

        return ['schools_cached' => $schools, 'students_cached' => $students];
    }

    /** Stream-build students -> attend:students (field = rfid, value = JSON) */
    public function rebuildStudents(): int
    {
        $tmp = self::STUDENTS_KEY . ':tmp:' . Str::uuid();

        Student::query()
            ->where('is_on_whatsapp', 1)
            ->whereNotNull('rfid')
            ->select(['id', 'rfid', 'name', 'guardian_contact'])
            ->chunkById(self::STUDENT_DB_CHUNK, function ($chunk) use ($tmp) {
                Redis::pipeline(function ($pipe) use ($chunk, $tmp) {
                    foreach ($chunk as $s) {
                        $pipe->hset($tmp, $s->rfid, json_encode([
                            'id'               => $s->id,
                            'name'             => $s->name,
                            'guardian_contact' => $s->guardian_contact,
                        ], JSON_UNESCAPED_UNICODE));
                    }
                });
            });

        // Atomic swap (handle empty dataset safely)
        if (Redis::exists($tmp)) {
            Redis::rename($tmp, self::STUDENTS_KEY);
        } else {
            Redis::del(self::STUDENTS_KEY);
        }

        return (int) Redis::hlen(self::STUDENTS_KEY);
    }

    /**
     * Stream-build schools -> attend:schools
     * field = device.mac_address (push_to_server), value = JSON(school row)
     *
     * Uses Device as the driving table for efficient chunking.
     */
    public function rebuildSchools(): int
    {
        $tmp = self::SCHOOLS_KEY . ':tmp:' . Str::uuid();

        Device::query()
            ->where('devices.type', 'push_to_server')
            ->join('schools', 'schools.id', '=', 'devices.school_id')
            ->leftJoin('school_settings', 'school_settings.school_id', '=', 'schools.id')
            ->orderBy('devices.id') // required for chunkById
            ->select([
                'devices.id as id',
                'devices.mac_address',
                'schools.id as school_id',
                'schools.name',
                'school_settings.checkin_start',
                'school_settings.checkin_end',
                'school_settings.checkout_start',
                'school_settings.checkout_end',
            ])
            ->chunkById(self::SCHOOL_DB_CHUNK, function ($chunk) use ($tmp) {
                Redis::pipeline(function ($pipe) use ($chunk, $tmp) {
                    foreach ($chunk as $row) {
                        $payload = [
                            'id'             => $row->school_id,
                            'name'           => $row->name,
                            'checkin_start'  => $row->checkin_start,
                            'checkin_end'    => $row->checkin_end,
                            'checkout_start' => $row->checkout_start,
                            'checkout_end'   => $row->checkout_end,
                        ];
                        $pipe->hset($tmp, implode('-', explode(':', $row->mac_address)), json_encode($payload, JSON_UNESCAPED_UNICODE));
                    }
                });
            }, 'id'); // id column for chunkById

        if (Redis::exists($tmp)) {
            Redis::rename($tmp, self::SCHOOLS_KEY);
        } else {
            Redis::del(self::SCHOOLS_KEY);
        }

        return (int) Redis::hlen(self::SCHOOLS_KEY);
    }

    /* ----- Incremental upserts/deletes (same as before, safe for 100k) ----- */

    public static function upsertStudent(Student $s): void
    {
        // remove if off WhatsApp or RFID missing
        if (!$s->is_on_whatsapp || !$s->rfid) {
            Redis::hdel(self::STUDENTS_KEY, $s->rfid);
            return;
        }

        self::callWhatsappServerEndpointToUpdateRedis(self::STUDENTS_KEY, $s->rfid, json_encode([
            'id'               => $s->id,
            'name'             => $s->name,
            'guardian_contact' => $s->guardian_contact,
            'guardian_name'    => $s->guardian_name,
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
        $row = Device::query()
            ->where('schools.id', $schoolId)
            ->where('devices.type', 'push_to_server')
            ->join('schools', 'schools.id', '=', 'devices.school_id')
            ->leftJoin('school_settings', 'school_settings.school_id', '=', 'schools.id')
            ->select([
                'schools.id as school_id', 'schools.name',
                'school_settings.checkin_start', 'school_settings.checkin_end',
                'school_settings.checkout_start', 'school_settings.checkout_end',
                'devices.mac_address',
            ])
            ->first();

        $payload = [
            'id'             => $row->school_id,
            'name'           => $row->name,
            'checkin_start'  => $row->checkin_start,
            'checkin_end'    => $row->checkin_end,
            'checkout_start' => $row->checkout_start,
            'checkout_end'   => $row->checkout_end,
        ];

        self::callWhatsappServerEndpointToUpdateRedis(self::SCHOOLS_KEY, implode('-', explode(':', $row->mac_address)), json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public static function removeSchoolByMac(string $mac): void
    {
        Redis::hdel(self::SCHOOLS_KEY, $mac);
    }

    private static function callWhatsappServerEndpointToUpdateRedis($hash, $key, $value)
    {
        $response = \Http::post(env('WHATSAPP_URL') . '/redis/set', [
            'hash'  => $hash,
            'key'   => $key,
            'value' => $value,
        ]);

        if( $response->failed() ) {
            info('Failed to call WhatsApp server endpoint to update Redis cache');
        }
        info('Called WhatsApp server endpoint to update Redis cache');
    }
}
