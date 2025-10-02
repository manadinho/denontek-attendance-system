<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Owner;
use App\Models\User;
use DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {

            // Seed the schools table
            $shoolData = [
                'name'    => 'ABC Schools',
                'address' => '123, ABC Street, XYZ City',
            ];
            $shool = School::updateOrCreate(['name' => $shoolData['name']], $shoolData);

            // Add school settings
            $schoolSettingsData = [
                'school_id'     =>  $shool->id,
                'checkin_start' =>  '07:00',
                'checkin_end'   =>  '09:00',
                'checkout_start'=>  '11:00',
                'checkout_end'  =>  '13:00',
                'buffer_minutes' =>  '0',
            ];
            DB::table('school_settings')->updateOrInsert(['school_id' => $shool->id], $schoolSettingsData);

            // Seed the owners table
            $ownerData = [
                'name'     => 'Owner ABC Schools',
                'email'    => 'owner@school.com',
                'password' => \Hash::make('123456'),
            ];
            $owner = Owner::updateOrCreate(['email' => $ownerData['email']], $ownerData);

            // Seed the owner_school table
            $ownerSchoolData = [
                'owner_id'  => $owner->id,
                'school_id' => $shool->id,
            ];
            DB::table('owner_school')->insert($ownerSchoolData);

            // Seed the users table
            $userData = [
                'school_id' => $shool->id,
                'name'      => 'School Admin',
                'email'     => 'noumanisrar786@gmail.com',
                'contact'   => '03001234567',
                'password' => \Hash::make('123456'),
                'type'      => 'admin',
            ];
            User::updateOrCreate(['email' => $userData['email']], $userData);

            // seed the superamdin user
            $superAdminData = [
                'school_id' => $shool->id,
                'name'      => 'Super Admin',
                'email'     => 'superadmin@attendance.com',
                'contact'   => '03076929940',
                'password' => \Hash::make('Pa$$@0987'),
                'type'      => 'superadmin',
            ];
            User::updateOrCreate(['email' => $superAdminData['email']], $superAdminData);

            // Create Devices
            $deviceData = [[
                'school_id'  => $shool->id,
                'name'       => 'Push to Server Device',
                'mac_address' => '3C:61:05:11:DD:18',
                'type'       => 'push_to_server',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'name'       => 'Receiver Device',
                'mac_address' => 'eeeeeeeeeeeee',
                'type'       => 'receiver',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'name'       => 'Attendance Device 1',
                'mac_address' => 'C4:D8:D5:03:8E:33',
                'type'       => 'attendance',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'name'       => 'Registration Device 1',
                'mac_address' => 'C4:D8:D5:03:75:14',
                'type'       => 'registeration',
                'created_at' => now(),
            ]];
            DB::table('devices')->insert($deviceData);

            // Add default admin alerts
            $alertData = [
                'school_id' => $shool->id,
                'title'      => 'Daily Attendance Summary',
                'time'   => '11:00',
                'admin_contacts' => json_encode([]),
                'active'   => false,
                'created_at' => now(),
            ];

            DB::table('admin_alerts')->insert($alertData);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
