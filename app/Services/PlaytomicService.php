<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PlaytomicService
{
    private const BASE_URL = 'https://api.playtomic.io/v1/availability';

    /**
     * Fetch available court slots for a given date and Playtomic tenant.
     *
     * @return array|null Array of courts with slots, or null on failure.
     */
    public static function getAvailability(string $tenantId, string $date): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'sport_id' => 'PADEL',
                'tenant_id' => $tenantId,
                'start_min' => $date.'T00:00:00',
                'start_max' => $date.'T23:59:59',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get flattened list of available time slots for a date.
     * Returns: [['time' => '19:00', 'duration' => 60, 'price' => '52 GBP', 'court_id' => '...'], ...]
     */
    public static function getSlots(string $tenantId, string $date): array
    {
        $data = self::getAvailability($tenantId, $date);
        if (! $data) {
            return [];
        }

        $slots = [];
        $courtNum = 1;
        foreach ($data as $court) {
            foreach ($court['slots'] ?? [] as $slot) {
                $slots[] = [
                    'court_id' => $court['resource_id'] ?? 'unknown',
                    'court' => 'Court '.$courtNum,
                    'time' => substr($slot['start_time'], 0, 5), // "19:00"
                    'duration' => $slot['duration'],
                    'price' => $slot['price'] ?? '',
                ];
            }
            $courtNum++;
        }

        // Sort by time
        usort($slots, fn ($a, $b) => strcmp($a['time'], $b['time']));

        return $slots;
    }
}
