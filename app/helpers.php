<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Enquiry;

if (! function_exists('generateEnquiryName')) {
    function generateEnquiryName()
    {
        $latest = Enquiry::latest('id')->first();
        $number = $latest ? ($latest->id + 1) : 1;
        $formatted = str_pad($number, 5, '0', STR_PAD_LEFT);
        return 'ENQ-' . $formatted;
    }
}

if (!function_exists('randomenquiryGenerator')) {
    function randomenquiryGenerator()
    {
        do {
            // Generate a random 5-digit number (from 10000–99999)
            $randomNumber = 3 . rand(10000, 99999);

            // Check if this number already exists in the 'enquiry_name' column
            $exists = DB::table('enquiries')->where('slug', $randomNumber)->exists();
        } while ($exists); // Repeat if the number already exists

        return (string) $randomNumber;
    }
}


if (!function_exists('lookup_postcode')) {
    function lookup_postcode(string $postcode)
    {
        $apiKey = config('services.newpostcode.api_key'); // API key from config
        $postcode = strtoupper(str_replace(' ', '', $postcode)); // Normalize postcode

        $url = "https://api.easypostcodes.com/addresses/{$postcode}";

        try {
            $response = Http::withHeaders([
                'key' => $apiKey,
                'Accept' => 'application/json',
            ])->get($url);

            $data = [
                'addresses' => [],
                'post_town' => '',
            ];

            if ($response->successful()) {
                $results = $response->json();

                foreach ($results as $result) {
                    if (isset($result['envelopeAddress']['summaryLine'])) {
                        $data['addresses'][] = $result['envelopeAddress']['summaryLine'];
                    }

                    if (empty($data['post_town']) && isset($result['postTown'])) {
                        $data['post_town'] = $result['postTown'];
                    }
                }

                return $data;
            } else {
                Log::error('Postcode API error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Postcode lookup exception: ' . $e->getMessage());
            return null;
        }
    }
}


if (! function_exists('lookup_places')) {
    function lookup_places(string $region)
    {
        $apiKey = config('services.postcode.api_key'); // Get from config/services.php
        $region = strtoupper(str_replace(' ', '', $region)); // Clean and format input

        $url = "https://api.os.uk/search/names/v1/find?maxresults=1&query={$region}&key={$apiKey}";

        $response = Http::get($url);

        $data = [
            'county' => '',
            'region' => '',
        ];

        if ($response->successful()) {
            $check = json_decode($response->getBody(), true); // Decode as array

            if (isset($check['error'])) {
                Log::error('Postcode lookup error: ' . $check['error']['message']);
                return null;
            }

            if (!empty($check['results'][0]['GAZETTEER_ENTRY'])) {
                $entry = $check['results'][0]['GAZETTEER_ENTRY'];
                $data['county'] = $entry['COUNTY_UNITARY'] ?? '';
                $data['region'] = $entry['REGION'] ?? '';
                $data['country'] = $entry['COUNTRY'] ?? '';
            }
            return $data;
        } else {
            throw new Exception('Failed to fetch postcode: ' . $response->body());
        }
    }
}

if (!function_exists('formatPoundNumber')) {
    function formatPoundNumber($number, $precision = 1)
    {
        if (!is_numeric($number)) return '£0';

        if ($number < 1000) {
            return '£' . number_format($number, 0);
        }

        $units = ['', 'K', 'M', 'B', 'T'];
        $power = floor(log($number, 1000));
        $formatted = round($number / pow(1000, $power), $precision);

        return '£' . $formatted . $units[$power];
    }
}

if (!function_exists('beautify_status')) {
    function beautify_status(string $status): string
    {
        // Replace underscores or dashes with spaces and capitalize words
        return ucwords(str_replace(['_', '-'], ' ', $status));
    }
}
