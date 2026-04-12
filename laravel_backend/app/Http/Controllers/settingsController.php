<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class SettingsController extends Controller {
    use ApiResponse;

    public function index() {
        try {
            $settings = Setting::all()->pluck('value', 'key');
            return $this->success($settings, 'Settings retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to retrieve settings.');
        }
    }

    public function update(Request $request) {
        try {
            foreach ($request->all() as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            return $this->success(null, 'Settings saved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to save settings.');
        }
    }
}