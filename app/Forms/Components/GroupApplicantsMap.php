<?php

namespace App\Forms\Components;

use App\Filament\Resources\ApplicantResource;
use Exception;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenLocationCode\OpenLocationCode;

class GroupApplicantsMap extends Field
{
    protected string $view = 'forms.components.group-applicants-map';

    protected int $height = 500;

    protected array $center = [32.5149, -117.0382];

    protected int $zoom = 12;

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function center(array $center): static
    {
        $this->center = $center;

        return $this;
    }

    public function zoom(int $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getCenter(): array
    {
        return $this->center;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function getMapData(): array
    {
        $record = $this->getRecord();
        $points = [];
        $unmappable = [];

        if (! $record) {
            return ['points' => [], 'unmappable' => []];
        }

        $applicants = $record->applicants()->with('responses')->get();

        foreach ($applicants as $applicant) {
            $cityResponse = $applicant->responses->firstWhere('question_id', 17);

            $coords = null;
            $locationResponse = null;

            $responses = $applicant->responses->sortBy(fn($r) => $r->question_id == 45 ? 0 : 1);

            foreach ($responses as $resp) {
                if (empty($resp->user_response)) {
                    continue;
                }

                $currentCoords = $this->parseCoordinates($resp->user_response);

                if (! $currentCoords && preg_match('/https?:\/\/[^\s]+/i', $resp->user_response, $urlMatches)) {
                    $resolvedUrl = $this->resolveUrl($urlMatches[0]);
                    $currentCoords = $this->parseCoordinates($resolvedUrl);
                }

                if ($currentCoords) {
                    $coords = $currentCoords;
                    $locationResponse = $resp;
                    break;
                }

                if ($resp->question_id == 45) {
                    $locationResponse = $resp;
                }
            }

            $data = [
                'id' => $applicant->id,
                'name' => $applicant->applicant_name,
                'city' => $cityResponse?->user_response ?? 'No especificada',
                'response' => $locationResponse?->user_response,
                'url' => ApplicantResource::getUrl('view', ['record' => $applicant]),
                'map_url' => $locationResponse ? $this->extractLocationUrl($locationResponse->user_response) : null,
            ];

            if ($coords) {
                $data['lat'] = $coords['lat'];
                $data['lng'] = $coords['lng'];
                $points[] = $data;
            } else {
                $unmappable[] = $data;
            }
        }

        return [
            'points' => $points,
            'unmappable' => $unmappable,
        ];
    }

    protected function isShortenedGoogleMapsUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return (bool) preg_match('/(maps\.app\.goo\.gl|goo\.gl)/i', $url);
    }

    protected function resolveUrl(string $url): string
    {
        $url = trim($url);
        if (preg_match('/(https?:\/\/[^\s]+)/i', $url, $matches)) {
            $url = $matches[0];
        }

        return Cache::remember('resolved_url_' . md5($url), 60 * 24 * 30, function () use ($url) {
            try {
                $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
                
                $response = Http::withUserAgent($userAgent)->timeout(5)->head($url);
                $finalUrl = (string) $response->effectiveUri();

                if ($finalUrl === $url) {
                    $response = Http::withUserAgent($userAgent)->timeout(10)->get($url);
                    $finalUrl = (string) $response->effectiveUri();
                }

                return $finalUrl;
            } catch (Exception $e) {
                Log::warning('Failed to resolve URL: '.$url.' - '.$e->getMessage());
                return $url;
            }
        });
    }

    protected function parseCoordinates(?string $state): ?array
    {
        if (empty($state)) {
            return null;
        }
        $cleanState = trim($state);

        if (preg_match_all('/[-+]?\d+\.\d{6,}/', $cleanState, $matches)) {
            $nums = $matches[0];
            if (count($nums) >= 2) {
                for ($i = 0; $i < count($nums) - 1; $i++) {
                    $lat = (float) $nums[$i];
                    $lng = (float) $nums[$i+1];
                    if ($lat > 14 && $lat < 33 && $lng > -119 && $lng < -86) {
                        return ['lat' => $lat, 'lng' => $lng];
                    }
                }
                return ['lat' => (float) $nums[0], 'lng' => (float) $nums[1]];
            }
        }

        // 1. Local Plus Code support: e.g. "92X9+QCV Tijuana, Baja California"
        if (preg_match('/([23456789C][23456789CFGHJMPQRV][23456789CFGHJMPQRVWX]{6}\+[23456789CFGHJMPQRVWX]{2,7}|[23456789CFGHJMPQRVWX]{4,6}\+[23456789CFGHJMPQRVWX]{2,3})/i', $cleanState, $plusMatches)) {
            $plusCode = strtoupper($plusMatches[0]);
            
            try {
                $olc = new OpenLocationCode();
                $fullCode = $plusCode;

                if (!$olc->isFull($plusCode)) {
                    $fullCode = $olc->recoverNearest($plusCode, $this->center[0], $this->center[1]);
                }

                if ($olc->isFull($fullCode)) {
                    $decoded = $olc->decode($fullCode);
                    return [
                        'lat' => $decoded->latitudeCenter,
                        'lng' => $decoded->longitudeCenter,
                    ];
                }
            } catch (Exception $e) {
                Log::warning('Local plus code resolution failed: ' . $e->getMessage());
            }
        }

        // 2. Try to find the internal Google Maps format: !3dlat!4dlng (and encoded versions)
        if (preg_match('/(?:!3d|%213d)(?P<lat>[-+]?\d+(\.\d+)?)[^!%]*(?:!4d|%214d)(?P<lng>[-+]?\d+(\.\d+)?)/i', $cleanState, $matches)) {
            return ['lat' => (float) $matches['lat'], 'lng' => (float) $matches['lng']];
        }

        // 3. Try to find coordinates in the path: /place/lat,lng/ or after @
        if (preg_match('/(?:\/place\/|@)(?P<lat>[-+]?\d+(\.\d+)?),\s*(?P<lng>[-+]?\d+(\.\d+)?)/i', $cleanState, $matches)) {
            return ['lat' => (float) $matches['lat'], 'lng' => (float) $matches['lng']];
        }

        // 4. Try to find coordinates in query parameters: q=lat,lng or ll=lat,lng
        if (preg_match('/[?&](?:q|ll)=(?P<lat>[-+]?\d+(\.\d+)?),\s*(?P<lng>[-+]?\d+(\.\d+)?)/i', $cleanState, $matches)) {
            return ['lat' => (float) $matches['lat'], 'lng' => (float) $matches['lng']];
        }

        // 5. Pattern: Decimal coordinates (e.g. "32.5, -117.0") with strict boundaries
        if (preg_match('/(?<![\d.\-+])(?P<lat>[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?))\s*[, ]\s*(?P<lng>[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?))(?![\d.])/', $cleanState, $matches)) {
            return ['lat' => (float) $matches['lat'], 'lng' => (float) $matches['lng']];
        }

        // 6. Broad fallback: Look for any "number, number" pair anywhere in the string
        if (preg_match('/(?P<lat>[-+]?\d+\.\d{4,})\s*,\s*(?P<lng>[-+]?\d+\.\d{4,})/', $cleanState, $matches)) {
            return ['lat' => (float) $matches['lat'], 'lng' => (float) $matches['lng']];
        }

        // 7. Pattern: DMS coordinates
        if (preg_match('/(?P<lat_d>\d+)[°º\s]+(?P<lat_m>\d+)[\'’\s]+(?P<lat_s>\d+(?:\.\d+)?)[^NSns\d]+(?P<lat_dir>[NSns])\s+(?P<lng_d>\d+)[°º\s]+(?P<lng_m>\d+)[\'’\s]+(?P<lng_s>\d+(?:\.\d+)?)[^EWew\d]+(?P<lng_dir>[EWew])/i', $cleanState, $matches)) {
            $lat = (float) $matches['lat_d'] + ($matches['lat_m'] / 60) + ($matches['lat_s'] / 3600);
            if (strtoupper($matches['lat_dir']) === 'S') {
                $lat = -$lat;
            }
            $lng = (float) $matches['lng_d'] + ($matches['lng_m'] / 60) + ($matches['lng_s'] / 3600);
            if (strtoupper($matches['lng_dir']) === 'W') {
                $lng = -$lng;
            }

            return ['lat' => $lat, 'lng' => $lng];
        }

        return null;
    }

    protected function extractLocationUrl(?string $state): ?string
    {
        if (empty($state)) {
            return null;
        }
        $cleanState = trim($state);
        if (preg_match('/(https?:\/\/(www\.)?google\.[a-z.]+\/maps\/[^\s]+|https?:\/\/goo\.gl\/maps\/[^\s]+|https?:\/\/maps\.app\.goo\.gl\/[^\s]+)/i', $cleanState, $matches)) {
            return $matches[0];
        }
        if (preg_match('/(?<![\d.\-+])(?P<lat>[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?))\s*[, ]\s*(?P<lng>[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?))(?![\d.])/', $cleanState, $matches)) {
            return 'https://maps.google.com/?q='.urlencode(trim($matches[0]));
        }
        if (preg_match('/([23456789C][23456789CFGHJMPQRV][23456789CFGHJMPQRVWX]{6}\+[23456789CFGHJMPQRVWX]{2,7}|[23456789CFGHJMPQRVWX]{4,6}\+[23456789CFGHJMPQRVWX]{2,3})/i', $cleanState, $matches)) {
            return 'https://maps.google.com/?q='.urlencode($matches[0]);
        }

        return null;
    }
}
