<?php

namespace App\Services;

class SettingsService
{
    public function validateAdminPhoneNumbers($phoneNumbers)
    {
        $phoneNumbers = json_decode($phoneNumbers, true);

        $valid = true;
        $finalPhoneNumbers = [];

        foreach ($phoneNumbers as $phoneNumber) {
            if (!preg_match('/^\d{10,}$/', $phoneNumber['value']) || !preg_match('/^(92|03)\d{8,}$/', $phoneNumber['value'])) {
                $valid = false;
                break;
            }
            
            $cleanedPhoneNumber = str_replace(' ', '', $phoneNumber['value']);
            $finalPhoneNumbers[] = str_starts_with($cleanedPhoneNumber, '03') ? '92' . substr($cleanedPhoneNumber, 1) : $cleanedPhoneNumber ;
        }

        return ['valid' => $valid, 'message' => 'admin phone numbers are not correct', 'phone_numbers' => json_encode($finalPhoneNumbers)];
    }
}