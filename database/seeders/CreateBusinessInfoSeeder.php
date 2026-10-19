<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessInfo;
class CreateBusinessInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessinfo = BusinessInfo::first();
        $brandData = [
            'business_name' => 'Neoteric Digital',
            'business_brand_app_name' => 'NEO ERP',
            'business_logo' => 'assets/img/branding/neoteric-digital-logo.png',
            'business_favicon' => 'assets/img/favicon/favicon.ico',
            'business_brand_logo_on_top_left' => '<span class="app-brand-logo demo"><img src="/assets/img/branding/neoteric-icon.png" alt="Neoteric Digital" style="width: 34px; height: 34px; object-fit: contain;" /></span><span class="app-brand-text demo menu-text ms-2"><span class="brand-title">Neoteric</span><span class="brand-accent">DIGITAL</span><span class="brand-badge">NEO ERP</span></span>',
            'business_brand_credit_on_footer_enable' => '1',
            'business_brand_credit_on_footer' => '&copy; ' . date('Y') . ', created by <a href="https://neotericdigital.co.uk/" target="_blank" class="footer-link">Neoteric Digital</a>',
            'business_status' => 'Active',
            'business_short_name' => 'ND',
            'business_allowed_file_types' => 'IMG:.jpg,.jpeg,.png DOC:.pdf,.jpg,.jpeg,.png,.doc,.docx',
            'business_start_date' => date('Y-m-d'),
            'business_phone' => '0123 456 789',
            'business_email' => 'info@example.com',
            'business_website' => 'neotericdigital.co.uk',
        ];

        if ($businessinfo) {
            $businessinfo->update($brandData);
        } else {
            BusinessInfo::create($brandData);
        }
    }
}
