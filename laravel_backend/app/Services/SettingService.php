<?php

namespace App\Services;

use App\Models\Setting;

class SettingService {
    public function getSettings() {
        return Setting::all()->pluck('value', 'key')->toArray();
    }
}