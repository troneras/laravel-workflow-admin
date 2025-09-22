<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingsController extends Controller
{
    public function __construct(private SettingsService $settings) {}

    public function index(): Response
    {
        $queueSettings = $this->settings->getGroup('queue');

        return Inertia::render('settings/System', [
            'queueSettings' => $queueSettings,
            'settingsGroups' => [
                'queue' => 'Queue Configuration',
                // Future groups can be added here
            ],
        ]);
    }

    public function updateQueue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_timeout_seconds' => 'required|integer|min:60|max:86400', // 1 minute to 24 hours
            'dify_max_jobs_per_minute' => 'required|integer|min:1|max:60',
            'dify_max_concurrent_jobs' => 'required|integer|min:1|max:10',
            'dify_retry_after_seconds' => 'required|integer|min:10|max:300',
        ]);

        $this->settings->updateGroup('queue', $validated);

        return redirect()->back()->with('success', 'Queue settings updated successfully. Restart Horizon for timeout changes to take effect.');
    }
}
