<?php

namespace Database\Seeders;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\Fjob;
use App\Models\FjobAddress;
use App\Models\FjobQuotation;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\JobRetention;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\PlantHire;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Business Info
        $business = BusinessInfo::firstOrCreate(
            ['id' => 1],
            [
                'business_name' => 'Neoteric Construction Ltd',
                'business_email' => 'admin@neoerp.local',
                'business_phone' => '+44 (0) 20 7946 0192',
                'business_address' => 'Floor 24, The Leadenhall Building, 122 Leadenhall St, London EC3V 4AB',
                'business_status' => 'Active',
            ]
        );

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // 2. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@neoerp.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('NeoErp!Admin2026#Secure'),
                'business_id' => $business->id,
            ]
        );
        $admin->assignRole($superAdminRole);

        $workerDave = User::firstOrCreate(
            ['email' => 'dave.operative@neoerp.local'],
            [
                'name' => 'Dave Mason (Senior Joiner)',
                'password' => Hash::make('NeoErp!Dave2026'),
                'business_id' => $business->id,
            ]
        );

        $workerSarah = User::firstOrCreate(
            ['email' => 'sarah.engineer@neoerp.local'],
            [
                'name' => 'Sarah Campbell (Project Engineer)',
                'password' => Hash::make('NeoErp!Sarah2026'),
                'business_id' => $business->id,
            ]
        );

        // 3. Labor Rate Cards (Burdened Costing)
        LaborRateCard::updateOrCreate(
            ['user_id' => $workerDave->id],
            [
                'business_id' => $business->id,
                'base_hourly_rate' => 24.00,
                'overtime_multiplier_1_5' => 1.50,
                'overtime_multiplier_2_0' => 2.00,
                'ni_burden_percent' => 13.80,
                'pension_burden_percent' => 3.00,
                'overhead_burden_percent' => 10.00,
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]
        );

        LaborRateCard::updateOrCreate(
            ['user_id' => $workerSarah->id],
            [
                'business_id' => $business->id,
                'base_hourly_rate' => 32.00,
                'overtime_multiplier_1_5' => 1.50,
                'overtime_multiplier_2_0' => 2.00,
                'ni_burden_percent' => 13.80,
                'pension_burden_percent' => 3.00,
                'overhead_burden_percent' => 10.00,
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]
        );

        // 4. Customers
        $customer1 = Customer::firstOrCreate(
            ['company_name' => 'Landsec Developments PLC'],
            [
                'business_id' => $business->id,
                'contact_firstname' => 'Charles',
                'contact_lastname' => 'Hampton',
                'contact_email' => 'charles.hampton@landsec.co.uk',
                'contact_phone' => '020 7888 9999',
                'status' => 'active',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['company_name' => 'Berkeley Group Holdings'],
            [
                'business_id' => $business->id,
                'contact_firstname' => 'Marcus',
                'contact_lastname' => 'Sterling',
                'contact_email' => 'marcus.sterling@berkeleyhomes.co.uk',
                'contact_phone' => '020 8123 4567',
                'status' => 'active',
            ]
        );

        // 5. Suppliers
        $supTravis = Supplier::firstOrCreate(
            ['code' => 'SUP-TP-01'],
            [
                'business_id' => $business->id,
                'name' => 'Travis Perkins Commercial Supplies Ltd',
                'contact_name' => 'Arthur Pendelton',
                'email' => 'trade.london@travisperkins.co.uk',
                'payment_terms' => '30 Days Net',
                'status' => 'active',
            ]
        );

        $supSunbelt = Supplier::firstOrCreate(
            ['code' => 'SUP-SUNBELT-02'],
            [
                'business_id' => $business->id,
                'name' => 'Sunbelt Rentals UK (Plant & Access)',
                'contact_name' => 'Gemma Hughes (Hire Desk)',
                'email' => 'centralhire@sunbeltrentals.co.uk',
                'payment_terms' => '30 Days Net',
                'status' => 'active',
            ]
        );

        $supMarley = Supplier::firstOrCreate(
            ['code' => 'SUP-MARLEY-03'],
            [
                'business_id' => $business->id,
                'name' => 'Marley Roofing Supplies Ltd',
                'contact_name' => 'John Trade',
                'email' => 'sales@marleyroofing.co.uk',
                'payment_terms' => '30 Days Net',
                'status' => 'active',
            ]
        );

        // 6. Subcontractors (UK CIS Registered)
        $subPrecision = Subcontractor::firstOrCreate(
            ['utr_number' => '8723910482'],
            [
                'business_id' => $business->id,
                'company_name' => 'Precision Electrical Services Ltd',
                'trading_name' => 'Precision Power Systems',
                'contact_name' => 'Liam Gallagher',
                'email' => 'accounts@precisionelectrical.co.uk',
                'phone' => '020 7456 1234',
                'company_registration_number' => '11984210',
                'cis_tax_status' => 'standard', // 20%
                'cis_verification_number' => 'CIS-V-882910',
                'verified_at' => Carbon::now()->subMonths(3),
                'trade_specialism' => 'High Voltage & Commercial Fitout Electrical',
                'status' => 'active',
            ]
        );

        $subApex = Subcontractor::firstOrCreate(
            ['utr_number' => '5619283740'],
            [
                'business_id' => $business->id,
                'company_name' => 'Apex Mechanical & HVAC Contractors',
                'trading_name' => 'Apex Climate Solutions',
                'contact_name' => 'Ewan McGregor',
                'email' => 'billing@apexmechanical.co.uk',
                'phone' => '020 7981 7766',
                'company_registration_number' => '09847123',
                'cis_tax_status' => 'higher', // 30%
                'cis_verification_number' => 'CIS-V-993821',
                'verified_at' => Carbon::now()->subMonths(2),
                'trade_specialism' => 'Commercial HVAC, Ducting & Chilled Water Systems',
                'status' => 'active',
            ]
        );

        // 7. Flagship Project 1: Victoria Office HQ
        $job1 = Fjob::firstOrCreate(
            ['slug' => 'victoria-office-hq-fitout'],
            [
                'business_id' => $business->id,
                'customer_id' => $customer1->id,
                'enquiry_id' => 0,
                'lead_id' => 0,
                'job_name' => 'Victoria Office Headquarters Fitout - Phase 2',
                'job_title' => 'CAT B Commercial Office Fitout & Executive Boardrooms',
                'job_description' => 'Acoustic partitioning, high-end architectural glazing, MEP distribution, and complete interior finishes over 18,000 sq ft.',
                'job_type' => 'commercial',
                'job_priority' => 'high',
                'job_status' => 'in_progress',
                'assigned_to' => [$admin->id],
            ]
        );

        FjobQuotation::updateOrCreate(
            ['fjob_id' => $job1->id],
            [
                'quotation_id' => 101,
                'quotation_number' => 'QT-2026-0042',
                'quotation_date' => Carbon::now()->subMonths(2)->toDateString(),
                'valid_until' => Carbon::now()->addMonths(4)->toDateString(),
                'total_amount' => 250000.00,
                'status' => 'accepted',
            ]
        );

        FjobAddress::updateOrCreate(
            ['fjob_id' => $job1->id],
            [
                'contact_firstname' => 'Charles',
                'contact_lastname' => 'Hampton',
                'contact_phone' => '020 7888 9999',
                'contact_email' => 'charles.hampton@landsec.co.uk',
                'address' => '100 Victoria Street, Floor 6',
                'county' => 'Greater London',
                'postcode' => 'SW1E 5JL',
                'country' => 'United Kingdom',
            ]
        );

        // 8. Secondary Project 2: Battersea Power Station Unit 4
        $job2 = Fjob::firstOrCreate(
            ['slug' => 'battersea-ps-retail-u4'],
            [
                'business_id' => $business->id,
                'customer_id' => $customer2->id,
                'enquiry_id' => 0,
                'lead_id' => 0,
                'job_name' => 'Battersea Power Station Retail Unit 4 Fitout',
                'job_title' => 'Luxury Retail Unit Shell & Core Fitout',
                'job_description' => 'Mechanical HVAC ductwork, commercial electrical distribution, and decorative shopfront structural steel.',
                'job_type' => 'commercial',
                'job_priority' => 'medium',
                'job_status' => 'in_progress',
                'assigned_to' => [$admin->id],
            ]
        );

        FjobQuotation::updateOrCreate(
            ['fjob_id' => $job2->id],
            [
                'quotation_id' => 102,
                'quotation_number' => 'QT-2026-0043',
                'quotation_date' => Carbon::now()->subMonths(1)->toDateString(),
                'valid_until' => Carbon::now()->addMonths(5)->toDateString(),
                'total_amount' => 175000.00,
                'status' => 'accepted',
            ]
        );

        FjobAddress::updateOrCreate(
            ['fjob_id' => $job2->id],
            [
                'contact_firstname' => 'Marcus',
                'contact_lastname' => 'Sterling',
                'contact_phone' => '020 8123 4567',
                'contact_email' => 'marcus.sterling@berkeleyhomes.co.uk',
                'address' => 'Circus Road West, Nine Elms',
                'county' => 'Greater London',
                'postcode' => 'SW11 8AH',
                'country' => 'United Kingdom',
            ]
        );

        // 9. Commercial Variations (Change Orders)
        $var1 = JobVariation::firstOrCreate(
            ['variation_number' => 'VAR-2026-0001'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'title' => 'Executive Boardroom Acoustic Glazing & Integrated Baffles',
                'description' => 'Architectural change instruction #07: Upgrade standard double glazing to 42dB acoustic glass and supply perforated acoustic wall baffles.',
                'cost_amount' => 14000.00,
                'markup_percentage' => 25.00,
                'quoted_amount' => 17500.00,
                'status' => 'approved',
                'client_signer_name' => 'Charles Hampton (Project Director)',
                'client_signer_email' => 'charles.hampton@landsec.co.uk',
                'signature_data' => 'DATA_URL_ESIGN_VERIFIED',
                'signed_at' => Carbon::now()->subDays(5),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(5),
            ]
        );

        $var2 = JobVariation::firstOrCreate(
            ['variation_number' => 'VAR-2026-0002'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'title' => 'Additional Dual 32A Commando Sockets & Sub-Distribution Feed',
                'description' => 'Client tenant IT requirement: Additional comms rack power feeds directly from riser sub-board.',
                'cost_amount' => 3200.00,
                'markup_percentage' => 20.00,
                'quoted_amount' => 3840.00,
                'status' => 'submitted',
            ]
        );

        // 10. Procurement: Purchase Orders, Deliveries & 3-Way Matched Supplier Bills
        // PO 1: Clean 3-Way Match Approved (Travis Perkins)
        $po1 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-2026-0001'],
            [
                'business_id' => $business->id,
                'supplier_id' => $supTravis->id,
                'job_id' => $job1->id,
                'order_date' => Carbon::now()->subDays(20)->toDateString(),
                'expected_delivery_date' => Carbon::now()->subDays(15)->toDateString(),
                'status' => 'received',
                'tax_rate' => 20.00,
                'subtotal' => 18400.00,
                'tax_amount' => 3680.00,
                'total_amount' => 22080.00,
                'notes' => 'Call site manager 30 mins prior to delivery for offloading.',
                'created_by' => $admin->id,
            ]
        );

        $po1Item1 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'description' => 'Acoustic Insulation 100mm 1200x600mm Pack'],
            [
                'quantity' => 150,
                'received_quantity' => 150,
                'unit_price' => 42.00,
                'total_price' => 6300.00,
            ]
        );

        $po1Item2 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'description' => 'Steel Stud Partition Channel 70mm x 3.6m'],
            [
                'quantity' => 400,
                'received_quantity' => 400,
                'unit_price' => 14.50,
                'total_price' => 5800.00,
            ]
        );

        $po1Item3 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'description' => 'FireLine Tapered Edge Plasterboard 15mm 2.4x1.2m'],
            [
                'quantity' => 350,
                'received_quantity' => 350,
                'unit_price' => 18.00,
                'total_price' => 6300.00,
            ]
        );

        $grn1 = GoodsReceivedNote::firstOrCreate(
            ['grn_number' => 'GRN-2026-0001'],
            [
                'business_id' => $business->id,
                'purchase_order_id' => $po1->id,
                'delivery_note_number' => 'DN-TP-88410',
                'received_date' => Carbon::now()->subDays(15)->toDateString(),
                'received_by' => $admin->id,
                'notes' => 'All materials offloaded to 6th floor staging area in pristine condition.',
            ]
        );

        GoodsReceivedNoteItem::firstOrCreate(
            ['goods_received_note_id' => $grn1->id, 'purchase_order_item_id' => $po1Item1->id],
            ['quantity_received' => 150, 'condition' => 'good']
        );
        GoodsReceivedNoteItem::firstOrCreate(
            ['goods_received_note_id' => $grn1->id, 'purchase_order_item_id' => $po1Item2->id],
            ['quantity_received' => 400, 'condition' => 'good']
        );
        GoodsReceivedNoteItem::firstOrCreate(
            ['goods_received_note_id' => $grn1->id, 'purchase_order_item_id' => $po1Item3->id],
            ['quantity_received' => 350, 'condition' => 'good']
        );

        $bill1 = SupplierBill::firstOrCreate(
            ['bill_number' => 'BILL-TP-9821'],
            [
                'business_id' => $business->id,
                'internal_reference' => 'INT-BILL-0001',
                'purchase_order_id' => $po1->id,
                'supplier_id' => $supTravis->id,
                'job_id' => $job1->id,
                'bill_date' => Carbon::now()->subDays(12)->toDateString(),
                'due_date' => Carbon::now()->addDays(18)->toDateString(),
                'subtotal' => 18400.00,
                'tax_amount' => 3680.00,
                'total_amount' => 22080.00,
                'match_status' => 'approved',
                'approval_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(10),
            ]
        );

        SupplierBillItem::firstOrCreate(
            ['supplier_bill_id' => $bill1->id, 'purchase_order_item_id' => $po1Item1->id],
            [
                'description' => $po1Item1->description,
                'billed_quantity' => 150,
                'billed_unit_price' => 42.00,
                'billed_total' => 6300.00,
                'po_quantity' => 150,
                'po_unit_price' => 42.00,
                'grn_received_quantity' => 150,
                'quantity_variance' => 0.00,
                'price_variance_amount' => 0.00,
                'price_variance_percentage' => 0.00,
                'variance_type' => 'none',
            ]
        );
        SupplierBillItem::firstOrCreate(
            ['supplier_bill_id' => $bill1->id, 'purchase_order_item_id' => $po1Item2->id],
            [
                'description' => $po1Item2->description,
                'billed_quantity' => 400,
                'billed_unit_price' => 14.50,
                'billed_total' => 5800.00,
                'po_quantity' => 400,
                'po_unit_price' => 14.50,
                'grn_received_quantity' => 400,
                'quantity_variance' => 0.00,
                'price_variance_amount' => 0.00,
                'price_variance_percentage' => 0.00,
                'variance_type' => 'none',
            ]
        );
        SupplierBillItem::firstOrCreate(
            ['supplier_bill_id' => $bill1->id, 'purchase_order_item_id' => $po1Item3->id],
            [
                'description' => $po1Item3->description,
                'billed_quantity' => 350,
                'billed_unit_price' => 18.00,
                'billed_total' => 6300.00,
                'po_quantity' => 350,
                'po_unit_price' => 18.00,
                'grn_received_quantity' => 350,
                'quantity_variance' => 0.00,
                'price_variance_amount' => 0.00,
                'price_variance_percentage' => 0.00,
                'variance_type' => 'none',
            ]
        );

        // PO 2: Price Variance Alert On Commercial Hold (Marley Roofing)
        $po2 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-2026-0002'],
            [
                'business_id' => $business->id,
                'supplier_id' => $supMarley->id,
                'job_id' => $job1->id,
                'order_date' => Carbon::now()->subDays(10)->toDateString(),
                'expected_delivery_date' => Carbon::now()->subDays(5)->toDateString(),
                'status' => 'received',
                'tax_rate' => 20.00,
                'subtotal' => 6775.00,
                'tax_amount' => 1355.00,
                'total_amount' => 8130.00,
                'notes' => 'Timber palate curbside drop.',
                'created_by' => $admin->id,
            ]
        );

        $po2Item1 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po2->id, 'description' => 'Modern Slate Roof Tiles (Anthracite)'],
            [
                'quantity' => 3000,
                'received_quantity' => 3000,
                'unit_price' => 1.80,
                'total_price' => 5400.00,
            ]
        );

        $po2Item2 = PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po2->id, 'description' => 'Dry Ridge System Kit 6m'],
            [
                'quantity' => 25,
                'received_quantity' => 25,
                'unit_price' => 55.00,
                'total_price' => 1375.00,
            ]
        );

        $grn2 = GoodsReceivedNote::firstOrCreate(
            ['grn_number' => 'GRN-2026-0002'],
            [
                'business_id' => $business->id,
                'purchase_order_id' => $po2->id,
                'delivery_note_number' => 'DN-MR-4491',
                'received_date' => Carbon::now()->subDays(5)->toDateString(),
                'received_by' => $admin->id,
            ]
        );

        GoodsReceivedNoteItem::firstOrCreate(
            ['goods_received_note_id' => $grn2->id, 'purchase_order_item_id' => $po2Item1->id],
            ['quantity_received' => 3000, 'condition' => 'good']
        );
        GoodsReceivedNoteItem::firstOrCreate(
            ['goods_received_note_id' => $grn2->id, 'purchase_order_item_id' => $po2Item2->id],
            ['quantity_received' => 25, 'condition' => 'good']
        );

        // Supplier bill billed at £2.10 (+16.67% price variance -> commercial hold!)
        $bill2 = SupplierBill::firstOrCreate(
            ['bill_number' => 'BILL-MR-4412'],
            [
                'business_id' => $business->id,
                'internal_reference' => 'INT-BILL-0002',
                'purchase_order_id' => $po2->id,
                'supplier_id' => $supMarley->id,
                'job_id' => $job1->id,
                'bill_date' => Carbon::now()->subDays(3)->toDateString(),
                'due_date' => Carbon::now()->addDays(27)->toDateString(),
                'subtotal' => 7675.00,
                'tax_amount' => 1535.00,
                'total_amount' => 9210.00,
                'match_status' => 'variance_hold',
                'approval_status' => 'pending',
                'variance_notes' => 'Contractual unit price for Modern Slate Tiles is £1.80. Supplier invoice billed at £2.10 (+16.67% surge / £900.00 over PO budget). Disputed with supplier accounts.',
            ]
        );

        SupplierBillItem::firstOrCreate(
            ['supplier_bill_id' => $bill2->id, 'purchase_order_item_id' => $po2Item1->id],
            [
                'description' => $po2Item1->description,
                'billed_quantity' => 3000,
                'billed_unit_price' => 2.10,
                'billed_total' => 6300.00,
                'po_quantity' => 3000,
                'po_unit_price' => 1.80,
                'grn_received_quantity' => 3000,
                'quantity_variance' => 0.00,
                'price_variance_amount' => 0.30,
                'price_variance_percentage' => 16.67,
                'variance_type' => 'price_variance',
            ]
        );

        SupplierBillItem::firstOrCreate(
            ['supplier_bill_id' => $bill2->id, 'purchase_order_item_id' => $po2Item2->id],
            [
                'description' => $po2Item2->description,
                'billed_quantity' => 25,
                'billed_unit_price' => 55.00,
                'billed_total' => 1375.00,
                'po_quantity' => 25,
                'po_unit_price' => 55.00,
                'grn_received_quantity' => 25,
                'quantity_variance' => 0.00,
                'price_variance_amount' => 0.00,
                'price_variance_percentage' => 0.00,
                'variance_type' => 'none',
            ]
        );

        // 11. Plant & Equipment Hires
        // Plant 1: Active Scissor Lift
        PlantHire::firstOrCreate(
            ['hire_reference' => 'PH-2026-0001'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'supplier_id' => $supSunbelt->id,
                'item_name' => '14m Electric Scissor Lift (Dingli JCPT1412)',
                'category' => 'mewp',
                'serial_asset_tag' => 'SN-MEWP-9921',
                'start_date' => Carbon::now()->subDays(6)->toDateString(),
                'expected_end_date' => Carbon::now()->addDays(8)->toDateString(),
                'daily_rate' => 85.00,
                'weekly_rate' => 340.00,
                'delivery_charge' => 120.00,
                'collection_charge' => 120.00,
                'status' => 'active',
                'accrued_hire_cost' => 580.00,
                'notes' => 'Key held in site lockbox A4.',
            ]
        );

        // Plant 2: OVERDUE Standby Bleed Alert! (Expired 3 days ago)
        PlantHire::firstOrCreate(
            ['hire_reference' => 'PH-2026-0002'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'supplier_id' => $supSunbelt->id,
                'item_name' => '1.5T Mini Excavator (Kubota KX015-4)',
                'category' => 'excavator',
                'serial_asset_tag' => 'SN-EXC-4418',
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'expected_end_date' => Carbon::now()->subDays(3)->toDateString(),
                'daily_rate' => 120.00,
                'weekly_rate' => 480.00,
                'delivery_charge' => 150.00,
                'collection_charge' => 150.00,
                'status' => 'overdue',
                'accrued_hire_cost' => 1200.00,
                'notes' => 'Groundworks completed. Machine currently idle on site - off-hire immediately to halt daily bleed cost of £120/day!',
            ]
        );

        // Plant 3: Off-Hired Generator
        PlantHire::firstOrCreate(
            ['hire_reference' => 'PH-2026-0003'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'supplier_id' => $supSunbelt->id,
                'item_name' => '8kVA Super-Silenced Mobile Generator',
                'category' => 'generator',
                'serial_asset_tag' => 'SN-GEN-1029',
                'start_date' => Carbon::now()->subDays(20)->toDateString(),
                'expected_end_date' => Carbon::now()->subDays(6)->toDateString(),
                'actual_end_date' => Carbon::now()->subDays(6)->toDateString(),
                'daily_rate' => 50.00,
                'weekly_rate' => 200.00,
                'delivery_charge' => 80.00,
                'collection_charge' => 80.00,
                'status' => 'off_hired',
                'off_hire_reference' => 'OFF-HIRE-SB-99120',
                'off_hired_at' => Carbon::now()->subDays(6),
                'off_hired_by' => $admin->id,
                'accrued_hire_cost' => 480.00,
                'notes' => 'Collected by Sunbelt transport team.',
            ]
        );

        // 12. Subcontractor CIS Payment Certificates
        // Precision Electrical: Month 1 Claim (Certified & Paid)
        SubcontractorPaymentCertificate::firstOrCreate(
            ['certificate_number' => 'SPC-2026-0001'],
            [
                'business_id' => $business->id,
                'subcontractor_id' => $subPrecision->id,
                'job_id' => $job1->id,
                'period_start' => Carbon::now()->subDays(28)->toDateString(),
                'period_end' => Carbon::now()->subDays(14)->toDateString(),
                'tax_month' => Carbon::now()->format('Y-m'),
                'gross_amount' => 18000.00,
                'materials_cost' => 3500.00,
                'labor_amount' => 14500.00,
                'cis_tax_rate' => 20.00,
                'cis_deduction_amount' => 2900.00, // 20% of 14,500
                'net_payable' => 15100.00,
                'payment_status' => 'certified',
                'certified_by' => $admin->id,
                'certified_at' => Carbon::now()->subDays(12),
                'notes' => '1st interim payment for containment and sub-mains cabling.',
            ]
        );

        // Apex Mechanical: Month 1 Claim on Job 2 (Draft)
        SubcontractorPaymentCertificate::firstOrCreate(
            ['certificate_number' => 'SPC-2026-0002'],
            [
                'business_id' => $business->id,
                'subcontractor_id' => $subApex->id,
                'job_id' => $job2->id,
                'period_start' => Carbon::now()->subDays(14)->toDateString(),
                'period_end' => Carbon::now()->toDateString(),
                'tax_month' => Carbon::now()->format('Y-m'),
                'gross_amount' => 16000.00,
                'materials_cost' => 4000.00,
                'labor_amount' => 12000.00,
                'cis_tax_rate' => 30.00, // Higher rate deduction
                'cis_deduction_amount' => 3600.00, // 30% of 12,000
                'net_payable' => 12400.00,
                'payment_status' => 'draft',
                'notes' => 'HVAC primary ducting installation across units 4A and 4B.',
            ]
        );

        // 13. Operative Burdened Timesheets
        LaborTimesheet::firstOrCreate(
            ['timesheet_number' => 'TS-2026-0001'],
            [
                'business_id' => $business->id,
                'user_id' => $workerDave->id,
                'job_id' => $job1->id,
                'work_date' => Carbon::now()->subDays(7)->toDateString(),
                'travel_hours' => 2.0,
                'standard_hours' => 38.0,
                'overtime_1_5_hours' => 4.0,
                'overtime_2_0_hours' => 0.0,
                'total_hours' => 44.0,
                'cost_rate_per_hour' => 24.00,
                'burden_multiplier' => 1.2680,
                'direct_pay_amount' => 1104.00,
                'fully_burdened_cost' => 1399.87,
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(6),
                'notes' => 'Erection of metal stud partitioning and ceiling acoustic channels.',
            ]
        );

        LaborTimesheet::firstOrCreate(
            ['timesheet_number' => 'TS-2026-0002'],
            [
                'business_id' => $business->id,
                'user_id' => $workerSarah->id,
                'job_id' => $job1->id,
                'work_date' => Carbon::now()->subDays(7)->toDateString(),
                'travel_hours' => 0.0,
                'standard_hours' => 37.5,
                'overtime_1_5_hours' => 2.5,
                'overtime_2_0_hours' => 0.0,
                'total_hours' => 40.0,
                'cost_rate_per_hour' => 32.00,
                'burden_multiplier' => 1.2680,
                'direct_pay_amount' => 1320.00,
                'fully_burdened_cost' => 1673.76,
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(6),
                'notes' => 'Site engineering coordination, CAD clash detection, and setting out.',
            ]
        );

        // 14. Progress Valuations (Applications for Payment) & Retentions
        // Valuation 1: Month 1 (£60k Gross, £3k Retention held, Certified & Paid)
        $val1 = JobValuation::firstOrCreate(
            ['application_number' => 'APP-2026-0001'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'valuation_date' => Carbon::now()->subDays(25)->toDateString(),
                'period_end' => Carbon::now()->subDays(25)->toDateString(),
                'cumulative_work_value' => 55000.00,
                'materials_on_site_value' => 5000.00,
                'gross_valuation' => 60000.00,
                'previous_certified_amount' => 0.00,
                'current_gross_amount' => 60000.00,
                'retention_rate' => 5.00,
                'retention_deduction' => 3000.00,
                'net_claim_amount' => 57000.00,
                'certified_amount' => 57000.00,
                'status' => 'paid',
                'certified_at' => Carbon::now()->subDays(20),
                'certified_by' => $admin->id,
                'paid_at' => Carbon::now()->subDays(10)->toDateString(),
                'notes' => 'Application 01 approved in full by Landsec quantity surveyor.',
            ]
        );

        JobRetention::firstOrCreate(
            ['valuation_id' => $val1->id],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'retention_reference' => 'RET-2026-0001',
                'total_retained_amount' => 3000.00,
                'practical_completion_date' => Carbon::now()->addMonths(6)->toDateString(),
                'pc_release_amount' => 1500.00,
                'defects_expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                'defects_release_amount' => 1500.00,
                'status' => 'held',
            ]
        );

        // Valuation 2: Month 2 (£90k Cumulative Gross, Certified)
        $val2 = JobValuation::firstOrCreate(
            ['application_number' => 'APP-2026-0002'],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'valuation_date' => Carbon::now()->subDays(5)->toDateString(),
                'period_end' => Carbon::now()->subDays(5)->toDateString(),
                'cumulative_work_value' => 82000.00,
                'materials_on_site_value' => 8000.00,
                'gross_valuation' => 90000.00,
                'previous_certified_amount' => 60000.00,
                'current_gross_amount' => 30000.00,
                'retention_rate' => 5.00,
                'retention_deduction' => 1500.00,
                'net_claim_amount' => 28500.00,
                'certified_amount' => 28500.00,
                'status' => 'certified',
                'certified_at' => Carbon::now()->subDays(2),
                'certified_by' => $admin->id,
                'notes' => 'Application 02 certified under JCT Design & Build 2016 interim certificate.',
            ]
        );

        JobRetention::firstOrCreate(
            ['valuation_id' => $val2->id],
            [
                'business_id' => $business->id,
                'job_id' => $job1->id,
                'retention_reference' => 'RET-2026-0002',
                'total_retained_amount' => 1500.00,
                'practical_completion_date' => Carbon::now()->addMonths(6)->toDateString(),
                'pc_release_amount' => 750.00,
                'defects_expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                'defects_release_amount' => 750.00,
                'status' => 'held',
            ]
        );
    }
}
