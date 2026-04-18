<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    use ApiResponse;

    public function __construct(private SettingService $settingService) {}

    public function index()
    {
        try {
            $settings = $this->settingService->getSettings();
            return $this->success($settings, 'Settings retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to retrieve settings.');
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'clinic_name'      => 'required|string|max:255',
                'clinic_address'   => 'nullable|string|max:500',
                'clinic_phone'     => 'nullable|string|max:20',
                'clinic_email'     => 'nullable|email|max:255',
                'default_tax_rate' => 'nullable|numeric|min:0|max:100',
                'default_due_days' => 'nullable|integer|min:1',
                'invoice_footer'   => 'nullable|string|max:500',
            ]);

            foreach ($request->all() as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            return $this->success(null, 'Settings saved successfully.');
        } catch (ValidationException $e) {
            return $this->error(collect($e->errors())->flatten()->join(', '), 422);
        }
    }
}
