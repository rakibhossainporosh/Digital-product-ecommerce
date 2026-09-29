<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalPanelApiService
{
    /**
     * Check if supplier API is enabled globally in settings.
     */
    public function isEnabled(): bool
    {
        $enabled = setting('supplier_api_enabled', setting('panel_api_enabled', false));

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN) || $enabled === 'on';
    }

    /**
     * Get the configured API endpoint URL for key generation.
     */
    public function getApiUrl(): string
    {
        $url = setting('supplier_api_url') ?: setting('panel_api_url', 'https://licensemanager.site/api/v1/generate.php');

        return (string) ($url ?: 'https://licensemanager.site/api/v1/generate.php');
    }

    /**
     * Get the configured API Key / Secret Token.
     */
    public function getApiKey(): ?string
    {
        $key = setting('supplier_api_key') ?: setting('panel_api_key');

        return $key ? (string) $key : null;
    }

    /**
     * Get the catalog URL (derives catalog endpoint from base/generate URL).
     */
    public function getCatalogUrl(): string
    {
        $url = $this->getApiUrl();

        if (str_contains($url, 'generate.php')) {
            return str_replace('generate.php', 'catalog.php', $url);
        }

        return rtrim($url, '/').'/catalog';
    }

    /**
     * Get the balance URL (derives balance endpoint from base/generate URL).
     */
    public function getBalanceUrl(): string
    {
        $url = $this->getApiUrl();

        if (str_contains($url, 'generate.php')) {
            return str_replace('generate.php', 'balance.php', $url);
        }

        return rtrim($url, '/').'/balance';
    }

    /**
     * Generate license keys from the external panel API.
     *
     * @return array{ok: bool, keys: array<int, string>, error: ?string}
     */
    public function generateKey(int|string $providerProductId, int $quantity, string $orderNumber): array
    {
        if (! $this->isEnabled()) {
            return [
                'ok' => false,
                'keys' => [],
                'error' => 'Supplier API integration is disabled in settings.',
            ];
        }

        $apiKey = $this->getApiKey();
        $apiUrl = $this->getApiUrl();

        if (blank($apiKey) || blank($apiUrl)) {
            return [
                'ok' => false,
                'keys' => [],
                'error' => 'Supplier API URL or API Key is missing in admin settings.',
            ];
        }

        if (blank($providerProductId)) {
            return [
                'ok' => false,
                'keys' => [],
                'error' => 'Supplier Product ID is not set for this variant.',
            ];
        }

        $payload = [
            'product_id' => (int) $providerProductId,
            'quantity' => max(1, (int) $quantity),
            'idempotency_key' => 'order-'.$orderNumber,
        ];

        Log::info("ExternalPanelApiService: Requesting {$quantity} key(s) from {$apiUrl} for Order #{$orderNumber}", [
            'payload' => $payload,
        ]);

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($apiUrl, $payload);

            $data = $response->json();

            Log::info("ExternalPanelApiService: Response received for Order #{$orderNumber}", [
                'status' => $response->status(),
                'body' => $data,
            ]);

            if ($response->successful() && is_array($data) && (! empty($data['ok']) || ! empty($data['success']))) {
                $rawKeys = $data['data']['keys'] ?? $data['keys'] ?? $data['data'] ?? [];
                $keys = [];

                if (is_array($rawKeys)) {
                    foreach ($rawKeys as $item) {
                        if (is_array($item)) {
                            $extracted = $item['key'] ?? $item['full_value'] ?? $item['code'] ?? reset($item);
                            if ($extracted) {
                                $keys[] = $this->sanitizeKey((string) $extracted);
                            }
                        } elseif (is_string($item) || is_numeric($item)) {
                            $keys[] = $this->sanitizeKey((string) $item);
                        }
                    }
                }

                if (! empty($keys)) {
                    return [
                        'ok' => true,
                        'keys' => $keys,
                        'error' => null,
                    ];
                }
            }

            $errorMessage = $data['error']['message'] ?? $data['message'] ?? 'Supplier API returned error or empty keys.';

            return [
                'ok' => false,
                'keys' => [],
                'error' => $errorMessage,
            ];
        } catch (Throwable $e) {
            Log::error('ExternalPanelApiService Connection Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'ok' => false,
                'keys' => [],
                'error' => 'Connection to supplier API server failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Clean raw key string by removing timestamp or supplier metadata.
     */
    public function sanitizeKey(string $rawKey): string
    {
        $clean = trim($rawKey);

        if (str_contains($clean, ' : ')) {
            $clean = trim(explode(' : ', $clean)[0]);
        } elseif (preg_match('/^(.*?)\s*:\s*\d{4}[-\/]\d{2}[-\/]\d{2}/', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        return $clean;
    }

    /**
     * Test connection to the external supplier API.
     *
     * @return array{ok: bool, message: string, details?: mixed}
     */
    public function testConnection(?string $customUrl = null, ?string $customKey = null): array
    {
        $url = $customUrl ?: $this->getApiUrl();
        $key = $customKey ?: $this->getApiKey();

        if (empty($url) || empty($key)) {
            return [
                'ok' => false,
                'message' => 'Please provide both API Endpoint URL and API Key.',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'product_id' => 999999, // test ping with dummy product ID
                    'quantity' => 1,
                    'idempotency_key' => 'ping-'.time(),
                ]);

            $status = $response->status();
            $data = $response->json();

            if ($status === 401 || $status === 403) {
                return [
                    'ok' => false,
                    'message' => "Authentication Failed! The API Key is invalid or unauthorized (HTTP {$status}).",
                ];
            }

            if ($response->successful() && is_array($data)) {
                return [
                    'ok' => true,
                    'message' => 'Connection Successful! Supplier API responded correctly.',
                    'details' => $data,
                ];
            }

            return [
                'ok' => false,
                'message' => "Unexpected server response (HTTP {$status}): ".$response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Connection Failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Fetch supplier account balance with 30-second cache.
     */
    public function fetchBalance(bool $forceFresh = false): ?float
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $apiKey = $this->getApiKey();
        $balanceUrl = $this->getBalanceUrl();

        if (empty($apiKey) || empty($balanceUrl)) {
            return null;
        }

        if ($forceFresh) {
            Cache::forget('supplier_api_balance');
        }

        return Cache::remember('supplier_api_balance', 30, function () use ($balanceUrl, $apiKey): ?float {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($balanceUrl);

                if (! $response->successful()) {
                    Log::warning('ExternalPanelApiService: Failed to fetch balance. HTTP '.$response->status());

                    return null;
                }

                $data = $response->json();
                if (isset($data['ok']) && $data['ok'] === true && isset($data['balance'])) {
                    return (float) $data['balance'];
                }

                return null;
            } catch (Throwable $e) {
                Log::error('ExternalPanelApiService: Balance fetch exception: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Fetch product catalog from supplier API with 60-second caching.
     *
     * @return array<int, array{in_stock: bool, stock_count: int, name: string, price: float}>
     */
    public function fetchCatalog(bool $forceFresh = false): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $apiKey = $this->getApiKey();
        $catalogUrl = $this->getCatalogUrl();

        if (empty($apiKey) || empty($catalogUrl)) {
            return [];
        }

        if ($forceFresh) {
            Cache::forget('supplier_catalog_stock');
        }

        return Cache::remember('supplier_catalog_stock', 60, function () use ($catalogUrl, $apiKey): array {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($catalogUrl);

                if (! $response->successful()) {
                    Log::warning('ExternalPanelApiService: Failed to fetch catalog. HTTP '.$response->status());

                    return [];
                }

                $data = $response->json();
                $stockMap = [];

                $groups = $data['data']['groups'] ?? ($data['data'] ?? []);

                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        if (isset($group['products']) && is_array($group['products'])) {
                            foreach ($group['products'] as $prod) {
                                if (isset($prod['id'])) {
                                    $stockMap[(int) $prod['id']] = [
                                        'in_stock' => (bool) ($prod['in_stock'] ?? false),
                                        'stock_count' => (int) ($prod['stock_count'] ?? 0),
                                        'name' => (string) ($prod['name'] ?? ''),
                                        'price' => (float) ($prod['price'] ?? 0),
                                    ];
                                }
                            }
                        } elseif (isset($group['id'])) {
                            $stockMap[(int) $group['id']] = [
                                'in_stock' => (bool) ($group['in_stock'] ?? false),
                                'stock_count' => (int) ($group['stock_count'] ?? 0),
                                'name' => (string) ($group['name'] ?? ''),
                                'price' => (float) ($group['price'] ?? 0),
                            ];
                        }
                    }
                }

                return $stockMap;
            } catch (Throwable $e) {
                Log::error('ExternalPanelApiService: Catalog fetch exception: '.$e->getMessage());

                return [];
            }
        });
    }

    /**
     * Pre-check whether a variant can be fulfilled via local stock or supplier fallback.
     *
     * @return array{allowed: bool, source: string, reason: ?string}
     */
    public function canFulfillVariant(ProductVariant $variant, int $quantity = 1, bool $forceFresh = false): array
    {
        $quantity = max(1, $quantity);
        $localStock = (int) $variant->available_keys_count;

        // 1. Local inventory is completely sufficient
        if ($localStock >= $quantity) {
            return [
                'allowed' => true,
                'source' => 'local',
                'reason' => null,
            ];
        }

        // 2. Check supplier fallback
        $neededFromApi = $quantity - max(0, $localStock);

        if (blank($variant->api_provider_id) || ! $this->isEnabled()) {
            return [
                'allowed' => false,
                'source' => 'none',
                'reason' => 'This package is currently out of stock. Please check back later.',
            ];
        }

        $catalog = $this->fetchCatalog($forceFresh);
        $providerId = (int) $variant->api_provider_id;

        $providerItem = $catalog[$providerId] ?? null;
        if (! $providerItem || empty($providerItem['in_stock'])) {
            return [
                'allowed' => false,
                'source' => 'supplier_stock',
                'reason' => 'This package is currently out of stock on the server. Please check back later.',
            ];
        }

        if (isset($providerItem['stock_count']) && $providerItem['stock_count'] > 0 && $providerItem['stock_count'] < $neededFromApi) {
            return [
                'allowed' => false,
                'source' => 'supplier_stock',
                'reason' => "Only {$providerItem['stock_count']} key(s) available on supplier server. Please reduce quantity.",
            ];
        }

        $unitCost = (float) ($providerItem['price'] ?? 0);
        $totalCost = $unitCost * $neededFromApi;
        $balance = $this->fetchBalance($forceFresh);

        if ($balance !== null && $balance < $totalCost) {
            Log::warning("ExternalPanelApiService: Insufficient supplier balance ({$balance} USD) for needed {$neededFromApi}x of Provider #{$providerId} (Cost: {$totalCost} USD).");

            return [
                'allowed' => false,
                'source' => 'supplier_balance',
                'reason' => 'This package is temporarily unavailable due to server maintenance. Please check back later.',
            ];
        }

        return [
            'allowed' => true,
            'source' => $localStock > 0 ? 'hybrid' : 'supplier_api',
            'reason' => null,
        ];
    }
}
