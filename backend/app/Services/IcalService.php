<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class IcalService
{
    /**
     * Fetch and parse an iCal feed.
     * Returns an array of events: [['uid' => ..., 'start' => ..., 'end' => ...], ...]
     */
    public function fetchAndParse(string $url): array
    {
        try {
            $response = Http::timeout(15)->get($url);
            
            if (!$response->successful()) {
                Log::error("Failed to fetch iCal from URL: {$url}. Status: " . $response->status());
                return [];
            }

            return $this->parseIcal($response->body());
        } catch (Exception $e) {
            Log::error("Exception while fetching iCal from URL {$url}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Basic custom parser for VEVENT blocks.
     */
    protected function parseIcal(string $content): array
    {
        $events = [];
        $lines = explode("\n", str_replace("\r", "", $content));
        $inEvent = false;
        $currentEvent = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line === 'BEGIN:VEVENT') {
                $inEvent = true;
                $currentEvent = [];
                continue;
            }

            if ($line === 'END:VEVENT') {
                $inEvent = false;
                if ($this->isValidEvent($currentEvent)) {
                    $events[] = $currentEvent;
                }
                continue;
            }

            if ($inEvent) {
                // Parse properties like UID, DTSTART, DTEND
                // Example: DTSTART;VALUE=DATE:20260402 or DTSTART:20260402T140000Z
                if (str_starts_with($line, 'UID:')) {
                    $currentEvent['uid'] = substr($line, 4);
                } elseif (str_starts_with($line, 'DTSTART')) {
                    $currentEvent['start'] = $this->parseDateBlock($line);
                } elseif (str_starts_with($line, 'DTEND')) {
                    $currentEvent['end'] = $this->parseDateBlock($line);
                }
            }
        }

        return $events;
    }

    protected function parseDateBlock(string $line): ?string
    {
        // DTSTART;VALUE=DATE:20260402 -> 20260402
        // DTSTART:20260402T140000Z -> 20260402T140000Z
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $dateString = $parts[1];
            // Normalize to Y-m-d if it's Ymd
            if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $dateString, $matches)) {
                return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
            }
        }
        return null;
    }

    protected function isValidEvent(array $event): bool
    {
        return !empty($event['uid']) && !empty($event['start']) && !empty($event['end']);
    }
}
