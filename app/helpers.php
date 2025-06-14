<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $date = date('Ymd');
        $todayCount = Enquiry::whereDate('created_at', now()->toDateString())->count() + 1;
        $sequence = str_pad($todayCount, 4, '0', STR_PAD_LEFT);
        return $date . $sequence;
    }
}

if (! function_exists('lookup_postcode')) {
    function lookup_postcode(string $postcode)
    {
        $apiKey = config('services.postcode.api_key'); // API key from config
        $postcode = strtoupper(str_replace(' ', '', $postcode)); // Normalize postcode

        $url = "https://api.os.uk/search/places/v1/postcode?postcode={$postcode}&key={$apiKey}";

        $response = Http::get($url);

        $data = [
            'addresses' => [],
            'post_town' => '',
        ];

        if ($response->successful()) {
            $check = json_decode($response->getBody(), true);

            if (isset($check['error'])) {
                Log::error('Postcode lookup error: ' . $check['error']['message']);
                return null;
            }

            foreach ($check['results'] as $result) {
                if (isset($result['DPA']['ADDRESS'])) {
                    $data['addresses'][] = $result['DPA']['ADDRESS'];
                }

                if (empty($data['post_town']) && isset($result['DPA']['POST_TOWN'])) {
                    $data['post_town'] = $result['DPA']['POST_TOWN']; // Set once
                }
            }

            return $data;
        } else {
            throw new Exception('Failed to fetch postcode: ' . $response->body());
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
}
