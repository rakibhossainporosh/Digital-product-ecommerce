<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingService
{
    public const CACHE_KEY = 'app_settings_all_cache';

    /**
     * Retrieve a casted setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->getCachedSettings();

        if (! array_key_exists($key, $settings)) {
            return $default;
        }

        $item = $settings[$key];

        return $this->castValue($item['value'], $item['type']);
    }

    /**
     * Create or update a setting.
     */
    public function set(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'string',
        bool $isPublic = false,
        ?string $description = null
    ): Setting {
        $storedValue = $this->serializeValue($value, $type);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $storedValue,
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description,
            ]
        );

        self::flushCache();

        return $setting;
    }

    /**
     * Retrieve all settings as key-value pairs with native type casting.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $settings = $this->getCachedSettings();
        $results = [];

        foreach ($settings as $key => $item) {
            $results[$key] = $this->castValue($item['value'], $item['type']);
        }

        return $results;
    }

    /**
     * Retrieve all public settings safe for client/frontend exposure.
     *
     * @return array<string, mixed>
     */
    public function getPublic(): array
    {
        $settings = $this->getCachedSettings();
        $results = [];

        foreach ($settings as $key => $item) {
            if (! empty($item['is_public'])) {
                $results[$key] = $this->castValue($item['value'], $item['type']);
            }
        }

        return $results;
    }

    /**
     * Check if application maintenance mode is enabled.
     */
    public function isMaintenanceMode(): bool
    {
        return (bool) $this->get('maintenance_mode', false);
    }

    /**
     * Flush settings cache.
     */
    public function clearCache(): void
    {
        self::flushCache();
    }

    /**
     * Static method to flush settings cache.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get raw settings from cache or database.
     *
     * @return array<string, array{value: ?string, type: string, is_public: bool, group: string}>
     */
    protected function getCachedSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::all()
                    ->keyBy('key')
                    ->map(fn (Setting $setting) => [
                        'value' => $setting->value,
                        'type' => $setting->type,
                        'is_public' => $setting->is_public,
                        'group' => $setting->group,
                    ])
                    ->all();
            } catch (Throwable) {
                return [];
            }
        });
    }

    /**
     * Cast raw database string to its designated native PHP type.
     */
    public function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true) ?? [],
            'encrypted' => $this->decryptValue($value),
            default => $value,
        };
    }

    /**
     * Serialize a native PHP value into raw database string for storage.
     */
    public function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            'encrypted' => $value !== '' ? Crypt::encryptString((string) $value) : '',
            default => (string) $value,
        };
    }

    /**
     * Safely decrypt encrypted values.
     */
    protected function decryptValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
}
