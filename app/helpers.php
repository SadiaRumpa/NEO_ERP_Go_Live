<?php

use App\Models\Enquiry;

if (! function_exists('generateEnquiryName')) {
    function generateEnquiryName() {
        $latest = Enquiry::latest('id')->first();
        $number = $latest ? ($latest->id + 1) : 1;
        $formatted = str_pad($number, 5, '0', STR_PAD_LEFT);
        return 'ENQ-' . $formatted;
    }
}
