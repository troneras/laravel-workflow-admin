<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    protected $cachePrefix = 'settings:';
    protected $cacheDuration = 300; // 5 minutes

    public function get(string $group, string $key, $default = null)
    {
        $cacheKey = $this->cachePrefix . $group . '.' . $key;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($group, $key, $default) {
            $setting = DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
                
            if (!$setting) {
                return $default;
            }
            
            return $this->castValue($setting->value, $setting->type);
        });
    }
    
    public function set(string $group, string $key, $value, string $type = 'string', ?string $description = null)
    {
        DB::table('settings')->updateOrInsert(
            ['group' => $group, 'key' => $key],
            [
                'value' => json_encode($value),
                'type' => $type,
                'description' => $description,
                'updated_at' => now(),
                'created_at' => DB::raw('IFNULL(created_at, NOW())'),
            ]
        );
        
        Cache::forget($this->cachePrefix . $group . '.' . $key);
        Cache::forget($this->cachePrefix . 'group:' . $group);
    }
    
    public function getGroup(string $group): array
    {
        return Cache::remember($this->cachePrefix . 'group:' . $group, $this->cacheDuration, function () use ($group) {
            $settings = DB::table('settings')
                ->where('group', $group)
                ->get();
                
            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => $this->castValue($setting->value, $setting->type)];
            })->toArray();
        });
    }
    
    public function updateGroup(string $group, array $settings)
    {
        DB::transaction(function () use ($group, $settings) {
            foreach ($settings as $key => $value) {
                // Get the existing setting to preserve its type
                $existing = DB::table('settings')
                    ->where('group', $group)
                    ->where('key', $key)
                    ->first();
                
                if ($existing) {
                    $this->set($group, $key, $value, $existing->type, $existing->description);
                }
            }
        });
        
        Cache::forget($this->cachePrefix . 'group:' . $group);
    }
    
    public function flush(): void
    {
        Cache::flush();
    }

    /**
     * Get job timeout in seconds
     */
    public function getJobTimeout(): int
    {
        return $this->get('queue', 'job_timeout_seconds', 14400); // Default 4 hours
    }

    /**
     * Set job timeout in seconds
     */
    public function setJobTimeout(int $seconds): void
    {
        $this->set('queue', 'job_timeout_seconds', $seconds, 'integer', 'Job execution timeout in seconds');
    }
    
    protected function castValue($value, $type)
    {
        $decoded = json_decode($value, true);
        
        return match ($type) {
            'integer' => (int) $decoded,
            'boolean' => (bool) $decoded,
            'array', 'json' => $decoded,
            default => $decoded,
        };
    }
}