<?php

namespace App\Services;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenseKeyImportService
{
    /**
     * Import raw license keys for a given product variant.
     *
     * @return array{
     *     total_lines: int,
     *     valid_keys: int,
     *     duplicates_in_batch: int,
     *     duplicates_in_db: int,
     *     imported: int,
     *     batch_ref: string
     * }
     */
    public function import(
        int $variantId,
        string $rawText,
        ?string $batchRef = null,
        ?string $notes = null,
        ?int $userId = null
    ): array {
        $variant = ProductVariant::findOrFail($variantId);
        $batch = $batchRef ?: 'BATCH-'.date('Ymd').'-'.strtoupper(Str::random(5));

        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $totalLines = count($lines);

        $cleanedKeys = [];
        $duplicatesInBatch = 0;

        foreach ($lines as $line) {
            $cleaned = $this->sanitizeKey($line);

            if ($cleaned === null || $cleaned === '') {
                continue;
            }

            if (isset($cleanedKeys[$cleaned])) {
                $duplicatesInBatch++;

                continue;
            }

            $cleanedKeys[$cleaned] = true;
        }

        $uniqueKeys = array_keys($cleanedKeys);
        $validKeysCount = count($uniqueKeys);

        if ($validKeysCount === 0) {
            return [
                'total_lines' => $totalLines,
                'valid_keys' => 0,
                'duplicates_in_batch' => $duplicatesInBatch,
                'duplicates_in_db' => 0,
                'imported' => 0,
                'batch_ref' => $batch,
            ];
        }

        // Fetch existing keys for this variant into a fast hash lookup map
        $existingKeys = LicenseKey::where('product_variant_id', $variant->id)
            ->pluck('key')
            ->flip()
            ->all();

        $keysToInsert = [];
        $duplicatesInDb = 0;

        foreach ($uniqueKeys as $key) {
            if (isset($existingKeys[$key])) {
                $duplicatesInDb++;

                continue;
            }

            $keysToInsert[] = $key;
        }

        $importedCount = 0;

        if (! empty($keysToInsert)) {
            DB::transaction(function () use ($variant, $keysToInsert, $batch, $notes, $userId, &$importedCount): void {
                $chunks = array_chunk($keysToInsert, 250);

                foreach ($chunks as $chunk) {
                    foreach ($chunk as $keyString) {
                        LicenseKey::create([
                            'product_variant_id' => $variant->id,
                            'key' => $keyString,
                            'status' => LicenseKeyStatus::Available,
                            'batch_ref' => $batch,
                            'notes' => $notes,
                            'created_by' => $userId,
                        ]);
                        $importedCount++;
                    }
                }
            });
        }

        return [
            'total_lines' => $totalLines,
            'valid_keys' => $validKeysCount,
            'duplicates_in_batch' => $duplicatesInBatch,
            'duplicates_in_db' => $duplicatesInDb,
            'imported' => $importedCount,
            'batch_ref' => $batch,
        ];
    }

    /**
     * Sanitize a single key line by removing supplier metadata, dates, and bracketed notes.
     */
    public function sanitizeKey(string $line): ?string
    {
        $cleaned = trim($line);

        if ($cleaned === '') {
            return null;
        }

        // Remove bracketed info, e.g.: [Expires: 2026-12-31] or (Supplier: VIP) or <note>
        $cleaned = preg_replace('/\[[^\]]*\]|\([^\)]*\)|\<[^\>]*\>/', '', $cleaned);

        // Remove trailing comment styles: // comment or # comment
        $cleaned = preg_replace('/(\/\/|#).*$/', '', $cleaned);

        // Remove pipe or semicolon trailing notes: KEY-1234 | 30 Days -> KEY-1234
        if (str_contains($cleaned, '|')) {
            $parts = explode('|', $cleaned);
            $cleaned = trim($parts[0]);
        }

        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : null;
    }
}
