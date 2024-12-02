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
                'password' => \Hash::make('123456'),
                'type'      => 'admin',
            ];
            User::updateOrCreate(['email' => $userData['email']], $userData);

            // seed the superamdin user
            $superAdminData = [
                'school_id' => $shool->id,
                'name'      => 'Super Admin',
                'email'     => 'superadmin@attendance.com',
                'password' => \Hash::make('Pa$$@0987'),
                'type'      => 'superadmin',
            ];
            User::updateOrCreate(['email' => $superAdminData['email']], $superAdminData);

            // Create Devices
            $deviceData = [[
                'school_id'  => $shool->id,
                'mac_address' => '3859534809',
                'chip_id' => '3859534809',
                'type'       => 'push_to_server',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'mac_address' => 'eeeeeeeeeeeee',
                'chip_id' => '3861788420',
                'type'       => 'receiver',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'mac_address' => 'wwwwwwwwwww',
                'chip_id' => '1665368377',
                'type'       => 'attendance',
                'created_at' => now(),
            ],
            [
                'school_id'  => $shool->id,
                'mac_address' => 'dddddddddddd',
                'chip_id' => '1637954209',
                'type'       => 'registeration',
                'created_at' => now(),
            ]];
            DB::table('devices')->insert($deviceData);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
