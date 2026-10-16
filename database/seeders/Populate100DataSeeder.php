<?php

namespace Database\Seeders;

use App\Models\BusinessInfo;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\EnquiryNote;
use App\Models\Fjob;
use App\Models\FjobAddress;
use App\Models\FjobNote;
use App\Models\FjobQuotation;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobRetention;
use App\Models\JobType;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\Lead;
use App\Models\LeadAddress;
use App\Models\LeadNote;
use App\Models\PlantHire;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockItemVariant;
use App\Models\StockWarehouse;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\User;
use App\Models\VehicleCategory;
use App\Models\VehicleDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class Populate100DataSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Business Info & Users
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

            $admin = User::firstOrCreate(
                ['email' => 'admin@neoerp.local'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('NeoErp!Admin2026#Secure'),
                    'business_id' => $business->id,
                ]
            );

            $teamMembers = [
                ['name' => 'Dave Mason (Senior Joiner)', 'email' => 'dave.operative@neoerp.local', 'rate' => 24.00],
                ['name' => 'Sarah Campbell (Project Engineer)', 'email' => 'sarah.engineer@neoerp.local', 'rate' => 32.00],
                ['name' => 'Marcus Vance (Commercial Director)', 'email' => 'marcus.vance@neoerp.local', 'rate' => 45.00],
                ['name' => 'James O\'Connor (Senior Site Manager)', 'email' => 'james.oconnor@neoerp.local', 'rate' => 35.00],
                ['name' => 'Priya Patel (Lead Quantity Surveyor)', 'email' => 'priya.patel@neoerp.local', 'rate' => 38.00],
                ['name' => 'Liam Wright (Master Electrician)', 'email' => 'liam.wright@neoerp.local', 'rate' => 28.50],
                ['name' => 'Elena Rostova (Architectural Fitout Lead)', 'email' => 'elena.rostova@neoerp.local', 'rate' => 36.00],
                ['name' => 'Michael Chang (Procurement Officer)', 'email' => 'michael.chang@neoerp.local', 'rate' => 26.00],
                ['name' => 'Tom Hardy (Heavy Plant Operator)', 'email' => 'tom.hardy@neoerp.local', 'rate' => 25.00],
                ['name' => 'Chloe Bennett (H&S Compliance Auditor)', 'email' => 'chloe.bennett@neoerp.local', 'rate' => 30.00],
            ];

            $userPool = [$admin];
            foreach ($teamMembers as $tm) {
                $user = User::firstOrCreate(
                    ['email' => $tm['email']],
                    [
                        'name' => $tm['name'],
                        'password' => Hash::make('NeoErp!Staff2026#'),
                        'business_id' => $business->id,
                    ]
                );

                LaborRateCard::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'business_id' => $business->id,
                        'base_hourly_rate' => $tm['rate'],
                        'overtime_multiplier_1_5' => 1.50,
                        'overtime_multiplier_2_0' => 2.00,
                        'ni_burden_percent' => 13.80,
                        'pension_burden_percent' => 3.00,
                        'overhead_burden_percent' => 10.00,
                        'effective_from' => '2026-01-01',
                        'is_active' => true,
                    ]
                );

                $userPool[] = $user;
            }

            // 2. Job Types
            $jobTypeDefinitions = [
                ['name' => 'Commercial Fitout', 'description' => 'Commercial CAT A/B Fitouts & Office Refurbishments'],
                ['name' => 'Mechanical & Electrical', 'description' => 'HVAC, Electrical Distribution, BMS & Fire Safety'],
                ['name' => 'Residential Construction', 'description' => 'High-end Residential, Mixed-Use & New Builds'],
                ['name' => 'Civil Engineering', 'description' => 'Groundworks, Substructures, Utilities & Drainage'],
                ['name' => 'Roofing & Cladding', 'description' => 'Single-Ply Membrane, Modern Slate, Standing Seam & Cladding'],
                ['name' => 'Facilities Maintenance', 'description' => 'Planned Preventative Maintenance & Reactive Facilities Works'],
            ];

            foreach ($jobTypeDefinitions as $jtd) {
                JobType::firstOrCreate(
                    ['name' => $jtd['name']],
                    [
                        'description' => $jtd['description'],
                    ]
                );
            }

            // 3. Stock Warehouses, Categories & Items
            $warehouses = [
                ['name' => 'Central London Depot (Bermondsey)', 'location' => 'Bermondsey Trading Estate, London SE16 3LL', 'description' => 'Primary hub for CAT B office fitout materials'],
                ['name' => 'Midlands Distribution Hub (Coventry)', 'location' => 'Prologis Park, Coventry CV6 4BX', 'description' => 'Bulk drylining, structural steel, and MEP staging'],
                ['name' => 'North West Storage Yard (Manchester)', 'location' => 'Trafford Park, Manchester M17 1EB', 'description' => 'Plant storage, heavy tooling, and architectural cladding'],
            ];

            $warehousePool = [];
            foreach ($warehouses as $wh) {
                $warehousePool[] = StockWarehouse::firstOrCreate(
                    ['name' => $wh['name']],
                    [
                        'location' => $wh['location'],
                        'description' => $wh['description'],
                        'is_active' => true,
                    ]
                );
            }

            $stockCats = [
                ['name' => 'Drylining & Partitions', 'description' => 'Plasterboard, metal studs, insulation & acoustic barriers'],
                ['name' => 'Electrical & Data', 'description' => 'Containment, distribution boards, LED lighting & Cat6 cabling'],
                ['name' => 'Plumbing & HVAC', 'description' => 'Copper pipework, ducting, valves & air conditioning units'],
                ['name' => 'Timber & Joinery', 'description' => 'Architectural timber, fire doors, skirtings & acoustic baffles'],
                ['name' => 'Structural Steel & Fixings', 'description' => 'Unistrut, brackets, heavy anchor bolts & beam clamps'],
                ['name' => 'Safety & PPE', 'description' => 'Hard hats, hi-vis, fall arrest harnesses & first aid kits'],
            ];

            $stockCatPool = [];
            foreach ($stockCats as $sc) {
                $stockCatPool[] = StockCategory::firstOrCreate(
                    ['name' => $sc['name']],
                    [
                        'description' => $sc['description'],
                        'is_active' => true,
                    ]
                );
            }

            $stockItemNames = [
                ['name' => 'FireLine Tapered Edge Plasterboard 15mm 2.4x1.2m', 'cat' => 0],
                ['name' => 'Steel Stud Partition Channel 70mm x 3.6m', 'cat' => 0],
                ['name' => 'Acoustic Insulation 100mm 1200x600mm Pack', 'cat' => 0],
                ['name' => '600x600mm Suspended Ceiling Tile (Dune eVo)', 'cat' => 0],
                ['name' => 'Low Smoke Zero Halogen 2.5mm Twin & Earth 100m', 'cat' => 1],
                ['name' => '600x600 LED Panel Light 40W 4000K', 'cat' => 1],
                ['name' => 'Galvanised Steel Cable Tray 150mm x 3m', 'cat' => 1],
                ['name' => 'Cat6A U/FTP Shielded Network Cable 305m Reel', 'cat' => 1],
                ['name' => 'Spiral Galvanised Ducting 200mm Dia x 3m', 'cat' => 2],
                ['name' => 'Daikin VRV Indoor Fan Coil Unit 3.6kW', 'cat' => 2],
                ['name' => '28mm End Feed Copper Coupling (Pack of 10)', 'cat' => 2],
                ['name' => 'FD30 Fire Rated Solid Core Oak Veneer Door', 'cat' => 3],
                ['name' => 'Slotted Unistrut Channel 41x41mm Pre-Galv 3m', 'cat' => 4],
                ['name' => 'M12 Through Bolt Heavy Duty Anchors (Box of 50)', 'cat' => 4],
                ['name' => 'JSP EVO3 Safety Helmet with Slip Ratchet', 'cat' => 5],
            ];

            $stockItemPool = [];
            foreach ($stockItemNames as $sin) {
                $stockItemPool[] = StockItem::firstOrCreate(
                    ['name' => $sin['name']],
                    [
                        'business_id' => $business->id,
                        'category_id' => $stockCatPool[$sin['cat']]->id,
                        'description' => 'Commercial grade construction specification.',
                        'is_active' => true,
                    ]
                );
            }

            $variantsData = [
                ['stock_item_id' => $stockItemPool[0]->id, 'name' => 'Standard 2.4m x 1.2m Sheet', 'sku' => 'PLAST-FL-24', 'quantity' => 350, 'unit' => 'sheet', 'avg_price' => 14.80, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[0]->id, 'name' => 'Long 3.0m x 1.2m Sheet', 'sku' => 'PLAST-FL-30', 'quantity' => 120, 'unit' => 'sheet', 'avg_price' => 18.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[1]->id, 'name' => '70mm x 3.6m Track & Stud', 'sku' => 'STUD-70-36', 'quantity' => 480, 'unit' => 'length', 'avg_price' => 8.25, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[1]->id, 'name' => '50mm x 3.0m Slim Channel', 'sku' => 'STUD-50-30', 'quantity' => 220, 'unit' => 'length', 'avg_price' => 6.90, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[2]->id, 'name' => '100mm Rockwool Pack (5.76m²)', 'sku' => 'INSU-RW-100', 'quantity' => 180, 'unit' => 'pack', 'avg_price' => 34.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[2]->id, 'name' => '50mm Acoustic Roll (14.4m²)', 'sku' => 'INSU-RW-50', 'quantity' => 75, 'unit' => 'roll', 'avg_price' => 28.00, 'warehouse_id' => $warehousePool[2]->id],
                ['stock_item_id' => $stockItemPool[3]->id, 'name' => 'Board Edge 600x600 (Box of 16)', 'sku' => 'CEIL-DUNE-BE', 'quantity' => 95, 'unit' => 'box', 'avg_price' => 42.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[3]->id, 'name' => 'Tegular Edge 600x600 (Box of 16)', 'sku' => 'CEIL-DUNE-TE', 'quantity' => 8, 'unit' => 'box', 'avg_price' => 48.50, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[4]->id, 'name' => '2.5mm² 100m Drum LSZH White', 'sku' => 'ELEC-LSZH-25', 'quantity' => 85, 'unit' => 'drum', 'avg_price' => 68.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[4]->id, 'name' => '1.5mm² 100m Drum LSZH White', 'sku' => 'ELEC-LSZH-15', 'quantity' => 110, 'unit' => 'drum', 'avg_price' => 46.20, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[4]->id, 'name' => '4.0mm² 100m Drum LSZH White', 'sku' => 'ELEC-LSZH-40', 'quantity' => 6, 'unit' => 'drum', 'avg_price' => 105.00, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[5]->id, 'name' => 'Standard 40W Cool White 4000K', 'sku' => 'LIGHT-LED-40W', 'quantity' => 240, 'unit' => 'pcs', 'avg_price' => 24.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[5]->id, 'name' => 'Emergency 3hr Battery Pack 40W', 'sku' => 'LIGHT-LED-EMERG', 'quantity' => 45, 'unit' => 'pcs', 'avg_price' => 52.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[6]->id, 'name' => 'Medium Duty 150mm x 3m', 'sku' => 'TRAY-GALV-150', 'quantity' => 160, 'unit' => 'length', 'avg_price' => 19.80, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[6]->id, 'name' => 'Heavy Duty 300mm x 3m', 'sku' => 'TRAY-GALV-300', 'quantity' => 80, 'unit' => 'length', 'avg_price' => 31.20, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[7]->id, 'name' => 'Cat6A LSZH Blue 305m Box', 'sku' => 'DATA-CAT6A-BLU', 'quantity' => 40, 'unit' => 'reel', 'avg_price' => 118.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[7]->id, 'name' => 'Cat6A LSZH Violet 305m Box', 'sku' => 'DATA-CAT6A-VIO', 'quantity' => 5, 'unit' => 'reel', 'avg_price' => 122.50, 'warehouse_id' => $warehousePool[2]->id],
                ['stock_item_id' => $stockItemPool[8]->id, 'name' => '200mm Dia x 3m Length', 'sku' => 'DUCT-SPIR-200', 'quantity' => 65, 'unit' => 'length', 'avg_price' => 38.40, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[8]->id, 'name' => '250mm Dia x 3m Length', 'sku' => 'DUCT-SPIR-250', 'quantity' => 30, 'unit' => 'length', 'avg_price' => 49.50, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[9]->id, 'name' => 'FXSQ32A Slim Concealed 3.6kW', 'sku' => 'HVAC-DKN-36', 'quantity' => 12, 'unit' => 'pcs', 'avg_price' => 720.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[9]->id, 'name' => 'FXSQ50A Medium Concealed 5.6kW', 'sku' => 'HVAC-DKN-56', 'quantity' => 4, 'unit' => 'pcs', 'avg_price' => 940.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[10]->id, 'name' => '28mm Straight Coupling (Bag 10)', 'sku' => 'PLMB-CU-28C', 'quantity' => 85, 'unit' => 'pack', 'avg_price' => 16.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[10]->id, 'name' => '22mm Straight Coupling (Bag 10)', 'sku' => 'PLMB-CU-22C', 'quantity' => 140, 'unit' => 'pack', 'avg_price' => 11.20, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[10]->id, 'name' => '15mm Straight Coupling (Bag 10)', 'sku' => 'PLMB-CU-15C', 'quantity' => 7, 'unit' => 'pack', 'avg_price' => 7.80, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[11]->id, 'name' => '1981 x 838 x 44mm Pre-Finished', 'sku' => 'DOOR-FD30-838', 'quantity' => 28, 'unit' => 'pcs', 'avg_price' => 165.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[11]->id, 'name' => '2040 x 926 x 44mm Commercial Metric', 'sku' => 'DOOR-FD30-926', 'quantity' => 14, 'unit' => 'pcs', 'avg_price' => 185.00, 'warehouse_id' => $warehousePool[1]->id],
                ['stock_item_id' => $stockItemPool[12]->id, 'name' => '41x41mm Heavy Gauge 2.5mm 3m', 'sku' => 'STRUT-4141-3M', 'quantity' => 210, 'unit' => 'length', 'avg_price' => 22.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[12]->id, 'name' => '41x21mm Shallow Channel 3m', 'sku' => 'STRUT-4121-3M', 'quantity' => 130, 'unit' => 'length', 'avg_price' => 16.80, 'warehouse_id' => $warehousePool[2]->id],
                ['stock_item_id' => $stockItemPool[13]->id, 'name' => 'M12 x 100mm Zinc Plated (Box 50)', 'sku' => 'FIX-TB-M12-100', 'quantity' => 60, 'unit' => 'box', 'avg_price' => 32.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[13]->id, 'name' => 'M10 x 80mm Zinc Plated (Box 50)', 'sku' => 'FIX-TB-M10-80', 'quantity' => 9, 'unit' => 'box', 'avg_price' => 24.00, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[14]->id, 'name' => 'White Site Manager Helmet', 'sku' => 'PPE-HELM-WHT', 'quantity' => 65, 'unit' => 'pcs', 'avg_price' => 12.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[14]->id, 'name' => 'High-Vis Yellow Site Operative Helmet', 'sku' => 'PPE-HELM-YEL', 'quantity' => 90, 'unit' => 'pcs', 'avg_price' => 12.50, 'warehouse_id' => $warehousePool[0]->id],
                ['stock_item_id' => $stockItemPool[14]->id, 'name' => 'Blue Electrical Specialist Helmet', 'sku' => 'PPE-HELM-BLU', 'quantity' => 8, 'unit' => 'pcs', 'avg_price' => 13.50, 'warehouse_id' => $warehousePool[1]->id],
            ];

            foreach ($variantsData as $vd) {
                StockItemVariant::firstOrCreate(
                    ['sku' => $vd['sku']],
                    array_merge($vd, ['is_active' => true])
                );
            }

            // 4. Vehicle Categories & Fleet Details
            $vehCats = [
                ['name' => 'Commercial Vans (Medium & LWB)', 'desc' => 'Mercedes Sprinter & Ford Transit site service vans'],
                ['name' => 'Site Utility Pickups (4x4)', 'desc' => 'Ford Ranger & Toyota Hilux site supervisor vehicles'],
                ['name' => 'Flatbed Trucks & Beavertails', 'desc' => '7.5T Iveco & DAF material delivery transport'],
            ];

            $vehCatPool = [];
            foreach ($vehCats as $vc) {
                $vehCatPool[] = VehicleCategory::firstOrCreate(
                    ['name' => $vc['name']],
                    ['description' => $vc['desc'], 'icon' => 'ti ti-truck', 'is_active' => true]
                );
            }

            $fleet = [
                ['reg' => 'LO24 XKP', 'make' => 'Mercedes-Benz', 'model' => 'Sprinter 315 CDI L3H2', 'fuel' => 'Diesel', 'year' => 2024, 'miles' => 14200, 'cat' => 0],
                ['reg' => 'EA73 WRT', 'make' => 'Ford', 'model' => 'Transit Custom 320 L2H1', 'fuel' => 'Diesel', 'year' => 2023, 'miles' => 28400, 'cat' => 0],
                ['reg' => 'BD22 YHN', 'make' => 'Ford', 'model' => 'Ranger Wildtrak 2.0 EcoBlue 4x4', 'fuel' => 'Diesel', 'year' => 2022, 'miles' => 41500, 'cat' => 1],
                ['reg' => 'KM25 PQL', 'make' => 'Toyota', 'model' => 'Hilux Invincible X 2.8 D-4D', 'fuel' => 'Diesel', 'year' => 2025, 'miles' => 8900, 'cat' => 1],
                ['reg' => 'VE23 NBM', 'make' => 'Iveco', 'model' => 'Eurocargo 75E19 Flatbed with Crane', 'fuel' => 'Diesel', 'year' => 2023, 'miles' => 33100, 'cat' => 2],
            ];

            foreach ($fleet as $f) {
                VehicleDetail::firstOrCreate(
                    ['registration_no' => $f['reg']],
                    [
                        'category_id' => $vehCatPool[$f['cat']]->id,
                        'vin_number' => 'VIN' . strtoupper(Str::random(14)),
                        'manufacturer' => $f['make'],
                        'model' => $f['model'],
                        'fuel_type' => $f['fuel'],
                        'year' => $f['year'],
                        'mileage' => $f['miles'],
                        'mot_expiry_date' => Carbon::now()->addMonths(8)->toDateString(),
                        'tax_expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
                        'insurance_expiry_date' => Carbon::now()->addMonths(10)->toDateString(),
                        'last_service_date' => Carbon::now()->subMonths(3)->toDateString(),
                        'next_service_date' => Carbon::now()->addMonths(9)->toDateString(),
                        'status' => 'Available',
                    ]
                );
            }

            // 5. Suppliers (15 UK Construction Suppliers)
            $supplierData = [
                ['code' => 'SUP-TP-01', 'name' => 'Travis Perkins Commercial Supplies Ltd', 'contact' => 'Arthur Pendelton', 'email' => 'trade.london@travisperkins.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-SUNBELT-02', 'name' => 'Sunbelt Rentals UK (Plant & Access)', 'contact' => 'Gemma Hughes', 'email' => 'centralhire@sunbeltrentals.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-MARLEY-03', 'name' => 'Marley Roofing Supplies Ltd', 'contact' => 'John Trade', 'email' => 'sales@marleyroofing.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-JEWSON-04', 'name' => 'Jewson Builders Merchants Ltd', 'contact' => 'Phil Armstrong', 'email' => 'commercial@jewson.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-REXEL-05', 'name' => 'Rexel UK Electrical Supplies', 'contact' => 'Simon Bradley', 'email' => 'projects@rexel.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-WOLSELEY-06', 'name' => 'Wolseley UK Commercial Plumbing & HVAC', 'contact' => 'Claire Jenkins', 'email' => 'tenders@wolseley.co.uk', 'terms' => '45 Days Net'],
                ['code' => 'SUP-SPEEDY-07', 'name' => 'Speedy Asset Services Ltd', 'contact' => 'Mark Robertson', 'email' => 'national.accounts@speedyservices.com', 'terms' => '30 Days Net'],
                ['code' => 'SUP-NATIONWIDE-08', 'name' => 'Nationwide Platforms (Loxam Group)', 'contact' => 'Hannah Scott', 'email' => 'hire@nationwideplatforms.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-HSS-09', 'name' => 'HSS Hire Service Group PLC', 'contact' => 'David Fletcher', 'email' => 'specialist.hire@hss.com', 'terms' => '30 Days Net'],
                ['code' => 'SUP-SIG-10', 'name' => 'SIG Distribution (Insulation & Interiors)', 'contact' => 'Rachel Adams', 'email' => 'london.interiors@sigplc.com', 'terms' => '60 Days Net'],
                ['code' => 'SUP-SCREWFIX-11', 'name' => 'Screwfix Commercial Trade Pro', 'contact' => 'Callum Davies', 'email' => 'commercial.desk@screwfix.com', 'terms' => '30 Days Net'],
                ['code' => 'SUP-SELCO-12', 'name' => 'Selco Builders Warehouse Commercial', 'contact' => 'Tony Rossi', 'email' => 'trade.b2b@selcobw.com', 'terms' => '30 Days Net'],
                ['code' => 'SUP-KEYLINE-13', 'name' => 'Keyline Civils Specialist UK', 'contact' => 'George Bates', 'email' => 'civils.uk@keyline.co.uk', 'terms' => '45 Days Net'],
                ['code' => 'SUP-CITYPLUMB-14', 'name' => 'City Plumbing Supplies Ltd', 'contact' => 'Emma Watson', 'email' => 'contracts@cityplumbing.co.uk', 'terms' => '30 Days Net'],
                ['code' => 'SUP-GRAFTON-15', 'name' => 'Grafton Merchanting GB', 'contact' => 'Robert Taylor', 'email' => 'accounts@graftonplc.co.uk', 'terms' => '30 Days Net'],
            ];

            $supplierPool = [];
            foreach ($supplierData as $sd) {
                $supplierPool[] = Supplier::firstOrCreate(
                    ['code' => $sd['code']],
                    [
                        'business_id' => $business->id,
                        'name' => $sd['name'],
                        'contact_name' => $sd['contact'],
                        'email' => $sd['email'],
                        'payment_terms' => $sd['terms'],
                        'status' => 'active',
                    ]
                );
            }

            // 6. Subcontractors (10 UK CIS Registered Contractors)
            $subcontractorData = [
                ['utr' => '8723910482', 'company' => 'Precision Electrical Services Ltd', 'trading' => 'Precision Power Systems', 'contact' => 'Liam Gallagher', 'email' => 'accounts@precisionelectrical.co.uk', 'phone' => '020 7456 1234', 'crn' => '11984210', 'cis' => 'standard', 'ver' => 'CIS-V-882910', 'trade' => 'High Voltage & Commercial Fitout Electrical'],
                ['utr' => '5619283740', 'company' => 'Apex Mechanical & HVAC Contractors', 'trading' => 'Apex Climate Solutions', 'contact' => 'Ewan McGregor', 'email' => 'billing@apexmechanical.co.uk', 'phone' => '020 7981 7766', 'crn' => '09847123', 'cis' => 'higher', 'ver' => 'CIS-V-993821', 'trade' => 'Commercial HVAC, Ducting & Chilled Water Systems'],
                ['utr' => '4410982736', 'company' => 'Vanguard Structural Steel & Glazing Ltd', 'trading' => 'Vanguard Structures', 'contact' => 'Derek Trotter', 'email' => 'info@vanguardsteel.co.uk', 'phone' => '0121 496 0888', 'crn' => '08712394', 'cis' => 'standard', 'ver' => 'CIS-V-771290', 'trade' => 'Architectural Steelwork, Balustrades & Curtain Walling'],
                ['utr' => '3398127456', 'company' => 'Starlight Drylining & Acoustic Ceilings Ltd', 'trading' => 'Starlight Interiors', 'contact' => 'Patrick O\'Shea', 'email' => 'claims@starlightdrylining.co.uk', 'phone' => '0161 834 9900', 'crn' => '10293847', 'cis' => 'standard', 'ver' => 'CIS-V-554412', 'trade' => 'SFS Light Gauge Steel Framing & SAS Acoustic Ceilings'],
                ['utr' => '9982341567', 'company' => 'Pyrosafe Commercial Fire Protection Ltd', 'trading' => 'Pyrosafe Systems', 'contact' => 'Victoria Cross', 'email' => 'admin@pyrosafeuk.co.uk', 'phone' => '020 8392 4411', 'crn' => '12093481', 'cis' => 'gross', 'ver' => 'CIS-V-009124', 'trade' => 'Fire Batt, Intumescent Mastic & Cavity Barriers'],
                ['utr' => '7723491823', 'company' => 'Titan Commercial Flooring Contractors Ltd', 'trading' => 'Titan Flooring', 'contact' => 'Gary Neville', 'email' => 'office@titanflooring.co.uk', 'phone' => '0113 245 7711', 'crn' => '07612349', 'cis' => 'standard', 'ver' => 'CIS-V-331902', 'trade' => 'Raised Access Flooring & Interface Carpet Tiles'],
                ['utr' => '6619028345', 'company' => 'Kestrel Demolition & Stripout Specialists Ltd', 'trading' => 'Kestrel Dismantling', 'contact' => 'Bill Sykes', 'email' => 'accounts@kestreldemo.co.uk', 'phone' => '020 8890 2200', 'crn' => '06591238', 'cis' => 'higher', 'ver' => 'CIS-V-118273', 'trade' => 'Internal Soft Stripout, Non-Structural Demolition & Asbestos'],
                ['utr' => '2291038475', 'company' => 'Pinnacle Scaffolding & Access Solutions Ltd', 'trading' => 'Pinnacle Scaffolding', 'contact' => 'Sean Connery', 'email' => 'hire@pinnaclescaffolding.co.uk', 'phone' => '0141 332 5500', 'crn' => 'SC192837', 'cis' => 'standard', 'ver' => 'CIS-V-449102', 'trade' => 'TG20:21 Compliant Heavy Duty System Scaffolding'],
                ['utr' => '1192837465', 'company' => 'Hydrotech Commercial Plumbing & Public Health', 'trading' => 'Hydrotech PH Services', 'contact' => 'Nigel Mansell', 'email' => 'accounts@hydrotechservices.co.uk', 'phone' => '0117 923 8811', 'crn' => '11827364', 'cis' => 'standard', 'ver' => 'CIS-V-662819', 'trade' => 'Commercial Public Health Drainage & Boosted Cold Water'],
                ['utr' => '8839201948', 'company' => 'Cladtech Facades & Building Envelopes Ltd', 'trading' => 'Cladtech Envelopes', 'contact' => 'Alistair Brownlee', 'email' => 'tenders@cladtechuk.co.uk', 'phone' => '0191 261 4400', 'crn' => '09182736', 'cis' => 'gross', 'ver' => 'CIS-V-882103', 'trade' => 'Rainscreen Cladding, Zinc Standing Seam & Louvres'],
            ];

            $subPool = [];
            foreach ($subcontractorData as $sub) {
                $subPool[] = Subcontractor::firstOrCreate(
                    ['utr_number' => $sub['utr']],
                    [
                        'business_id' => $business->id,
                        'company_name' => $sub['company'],
                        'trading_name' => $sub['trading'],
                        'contact_name' => $sub['contact'],
                        'email' => $sub['email'],
                        'phone' => $sub['phone'],
                        'company_registration_number' => $sub['crn'],
                        'cis_tax_status' => $sub['cis'],
                        'cis_verification_number' => $sub['ver'],
                        'verified_at' => Carbon::now()->subMonths(4),
                        'trade_specialism' => $sub['trade'],
                        'status' => 'active',
                    ]
                );
            }

            // 7. Generate 100 Realistic UK Customers
            $companyNames = [
                'Landsec Developments PLC', 'Berkeley Group Holdings', 'British Land Company PLC', 'Derwent London PLC',
                'Great Portland Estates PLC', 'Balfour Beatty Construction', 'Galliford Try Partnerships', 'Mace Group International',
                'Multiplex Construction Europe', 'ISG Construction Ltd', 'Kier Group Construction', 'Morgan Sindall Group',
                'Wates Group Construction', 'Sir Robert McAlpine Ltd', 'Skanska UK Construction', 'Lendlease Europe Construction',
                'Laing O\'Rourke Construction', 'Bowmer & Kirkland Ltd', 'Willmott Dixon Interiors', 'VolkerWessels UK Ltd',
                'Costain Group Civil Engineering', 'BAM Nuttall Infrastructure', 'Graham Construction Ltd', 'Bouygues UK Construction',
                'Overbury Commercial Fitout', 'McLaren Construction Ltd', 'Knight Frank Commercial Real Estate', 'CBRE Global Workplace Solutions',
                'JLL UK Commercial Property', 'Savills Real Estate Advisory', 'Cushman & Wakefield Commercial', 'British Airways Corporate HQ',
                'Tesco Distribution Property', 'Sainsbury\'s Property Estates', 'Marks & Spencer Commercial Estates', 'Heathrow Airport Commercial Facilities',
                'Gatwick Airport Commercial Property', 'King\'s College London Estates', 'University College London Estates', 'Imperial College Facilities',
                'Guy\'s & St Thomas\' NHS Foundation Trust', 'St George\'s University Hospitals NHS', 'Barts Health NHS Trust Estates', 'The Crown Estate UK',
                'Canary Wharf Group Property', 'Cadogan Commercial Estates', 'Grosvenor Britain & Ireland', 'Howard de Walden Estate',
                'Canary Wharf Finance Group', 'Legal & General Investment Management', 'Aviva Investors Real Assets', 'Standard Life Commercial Property',
                'Schroders Real Estate Capital', 'M&G Real Estate Commercial', 'Workspace Group Commercial Spaces', 'Derwent Valley Holdings',
                'Shaftesbury Capital PLC', 'Capital & Counties Properties', 'Argent Property Development', 'Stanhope PLC Property Developers',
                'Brookfield Properties UK', 'Tishman Speyer Europe', 'Hines Real Estate UK', 'AXA IM Real Assets Commercial',
                'BlackRock UK Property Fund', 'Oxford Properties Group UK', 'Gazeley UK Logistics Properties', 'Prologis UK Logistics Parks',
                'Segro Industrial Parks PLC', 'Tritax Big Box REIT', 'LondonMetric Property PLC', 'St. Modwen Properties PLC',
                'Harworth Group Urban Regeneration', 'Urban Splash Commercial', 'Countryside Partnerships PLC', 'Bellway Homes Commercial',
                'Redrow Commercial Ventures', 'Taylor Wimpey Mixed Use', 'Barratt Developments Commercial', 'Crest Nicholson Commercial',
                'Vistry Group Partnerships', 'Peabody Trust Commercial Services', 'Clarion Housing Commercial Estates', 'L&Q Living Commercial Developments',
                'Notting Hill Genesis Property', 'Hyde Housing Association Estates', 'Network Homes Commercial', 'Southern Housing Group Commercial',
                'Metropolitan Thames Valley Housing', 'Places for People Commercial', 'London Borough of Camden Property', 'Westminster City Council Property',
                'City of London Corporation Surveyors', 'Royal Borough of Kensington & Chelsea', 'London Borough of Hackney Estates', 'London Borough of Islington Property',
                'London Borough of Southwark Facilities', 'London Borough of Tower Hamlets', 'London Borough of Lambeth Estates', 'London Borough of Wandsworth Property'
            ];

            $firstNames = ['Charles', 'Marcus', 'Arthur', 'Gemma', 'John', 'Phil', 'Simon', 'Claire', 'Mark', 'Hannah', 'David', 'Rachel', 'Callum', 'Tony', 'George', 'Emma', 'Robert', 'Derek', 'Patrick', 'Victoria', 'Gary', 'Bill', 'Sean', 'Nigel', 'Alistair', 'Oliver', 'Charlotte', 'James', 'Sophie', 'William', 'Emily', 'Alexander', 'Jessica', 'Benjamin', 'Grace', 'Daniel', 'Alice', 'Matthew', 'Lucy', 'Joseph', 'Chloe'];
            $lastNames = ['Hampton', 'Sterling', 'Pendelton', 'Hughes', 'Trade', 'Armstrong', 'Bradley', 'Jenkins', 'Robertson', 'Scott', 'Fletcher', 'Adams', 'Davies', 'Rossi', 'Bates', 'Watson', 'Taylor', 'Trotter', 'O\'Shea', 'Cross', 'Neville', 'Sykes', 'Connery', 'Mansell', 'Brownlee', 'Sinclair', 'Fairfax', 'Montgomery', 'Crawford', 'Harrington', 'Cavendish', 'Kensington', 'Chamberlain', 'Wellington', 'Somerset', 'Beaumont', 'Fitzgerald', 'Ashford', 'Hawthorne', 'Vanguard', 'Lancaster'];

            $ukPostcodes = [
                ['addr' => '100 Victoria Street', 'county' => 'Greater London', 'postcode' => 'SW1E 5JL'],
                ['addr' => 'Circus Road West, Nine Elms', 'county' => 'Greater London', 'postcode' => 'SW11 8AH'],
                ['addr' => '25 Bank Street, Canary Wharf', 'county' => 'Greater London', 'postcode' => 'E14 5JP'],
                ['addr' => '100 Bishopsgate, Floor 18', 'county' => 'Greater London', 'postcode' => 'EC2N 4AG'],
                ['addr' => '1 Angel Court, Bank', 'county' => 'Greater London', 'postcode' => 'EC2R 7HJ'],
                ['addr' => 'Paddington Square, 1 Praed St', 'county' => 'Greater London', 'postcode' => 'W2 1EE'],
                ['addr' => 'King\'s Boulevard, King\'s Cross', 'county' => 'Greater London', 'postcode' => 'N1C 4AG'],
                ['addr' => 'Spinningfields Square, Deansgate', 'county' => 'Greater Manchester', 'postcode' => 'M3 3JE'],
                ['addr' => 'Colmore Row, Business District', 'county' => 'West Midlands', 'postcode' => 'B3 2BJ'],
                ['addr' => 'Temple Quay, Temple Back East', 'county' => 'Bristol', 'postcode' => 'BS1 6ED'],
                ['addr' => 'Wellington Place, Commercial Hub', 'county' => 'West Yorkshire', 'postcode' => 'LS1 4AP'],
                ['addr' => 'St Andrew Square, New Town', 'county' => 'City of Edinburgh', 'postcode' => 'EH2 2AF'],
                ['addr' => 'St Vincent Street, Financial Park', 'county' => 'Glasgow City', 'postcode' => 'G2 5TS'],
                ['addr' => 'Callaghan Square, Central South', 'county' => 'Cardiff', 'postcode' => 'CF10 5BT'],
                ['addr' => 'Science Park, Milton Road', 'county' => 'Cambridgeshire', 'postcode' => 'CB4 0GF'],
            ];

            $customerPool = [];
            for ($i = 0; $i < 100; $i++) {
                $cName = $companyNames[$i % count($companyNames)];
                if ($i >= count($companyNames)) {
                    $cName .= ' - Division ' . (intdiv($i, count($companyNames)) + 1);
                }

                $fn = $firstNames[$i % count($firstNames)];
                $ln = $lastNames[($i * 3) % count($lastNames)];
                $slugBase = Str::slug($cName);

                $customer = Customer::firstOrCreate(
                    ['company_name' => $cName],
                    [
                        'business_id' => $business->id,
                        'contact_firstname' => $fn,
                        'contact_lastname' => $ln,
                        'contact_email' => strtolower("{$fn}.{$ln}@{$slugBase}.co.uk"),
                        'contact_phone' => '020 ' . rand(7100, 7999) . ' ' . rand(1000, 9999),
                        'contact_mobile' => '07' . rand(700, 999) . ' ' . rand(100000, 999999),
                        'status' => 'active',
                    ]
                );

                $loc = $ukPostcodes[$i % count($ukPostcodes)];

                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_type' => 'billing'],
                    [
                        'contact_firstname' => $fn,
                        'contact_lastname' => $ln,
                        'contact_phone' => $customer->contact_phone,
                        'contact_email' => $customer->contact_email,
                        'address' => $loc['addr'],
                        'county' => $loc['county'],
                        'postcode' => $loc['postcode'],
                        'country' => 'United Kingdom',
                    ]
                );

                CustomerAddress::firstOrCreate(
                    ['customer_id' => $customer->id, 'address_type' => 'site'],
                    [
                        'contact_firstname' => $fn,
                        'contact_lastname' => $ln,
                        'contact_phone' => $customer->contact_phone,
                        'contact_email' => $customer->contact_email,
                        'address' => $loc['addr'] . ' (Project Site Gate 2)',
                        'county' => $loc['county'],
                        'postcode' => $loc['postcode'],
                        'country' => 'United Kingdom',
                    ]
                );

                CustomerDetails::firstOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'company_name' => $cName,
                        'registration_number' => '0' . rand(1000000, 9999999),
                        'assets' => rand(5000000, 75000000),
                        'net_assets' => rand(3000000, 45000000),
                        'liabilities' => rand(1000000, 20000000),
                        'cash_in_bank' => rand(800000, 15000000),
                    ]
                );

                $customerPool[] = $customer;
            }

            // 8. Generate 100 Enquiries
            $enquiryScopes = [
                'CAT A to CAT B Commercial Office Fitout Specification',
                'Acoustic Glass Partitioning & Executive Meeting Suites',
                'Complete Mechanical HVAC Ventilation & Heat Recovery Overhaul',
                'High-Bay Commercial LED Lighting & DALI Smart Controls',
                'Fire Compartmentation, Intumescent Coatings & FD60 Fire Doors',
                'Structural Glazing, Atrium Curtain Walling & Rooflights',
                'Commercial Public Health Drainage & Sub-Mains Electrical',
                'Raised Access Flooring System 600x600 Heavy Grade Installation',
                'Data Centre Clean Agent Fire Suppression & Riser Cabling',
                'Reception Atrium Feature Joinery & Architectural Slatted Timber',
            ];

            $enquirySources = ['Website Portal', 'Tender Portal', 'Phone Call', 'Architect Referral', 'Direct Tender Email'];
            $enquiryPriorities = ['urgent', 'high', 'medium', 'low'];
            $enquiryStatuses = ['new', 'new', 'new', 'on_hold', 'inprogress', 'completed'];

            for ($i = 1; $i <= 100; $i++) {
                $cust = $customerPool[($i - 1) % count($customerPool)];
                $scope = $enquiryScopes[$i % count($enquiryScopes)];
                $priority = $enquiryPriorities[$i % count($enquiryPriorities)];
                $status = $enquiryStatuses[$i % count($enquiryStatuses)];
                $loc = $ukPostcodes[$i % count($ukPostcodes)];

                $turnaround1 = 1 + (($i * 3) % 5);
                $turnaround2 = 3 + (($i * 7) % 9);
                $totalCycle = $turnaround1 + $turnaround2;
                $baseDaysAgo = 5 + $totalCycle + (int)(($i / 100.0) * 75);
                $enquiryDate = Carbon::now()->subDays($baseDaysAgo)->subHours(($i * 7) % 23);

                $enquiry = Enquiry::firstOrCreate(
                    ['enquiry_name' => "ENQ-2026-" . str_pad($i, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'enquiry' => "Client tender submission for {$scope} at {$cust->company_name}.",
                        'enquiry_description' => "Scope includes preliminary site survey, architectural drawing coordination, RFI submission, and detailed cost schedule for {$scope}.",
                        'customer_id' => $cust->id,
                        'enquiry_type' => 'commercial',
                        'enquiry_category' => 'fitout',
                        'enquiry_priority' => $priority,
                        'enquiry_source' => $enquirySources[$i % count($enquirySources)],
                        'enquiry_status' => $status,
                        'annual_maintenance' => ($i % 3 === 0),
                        'installations' => true,
                        'repairs' => ($i % 4 === 0),
                        'testing' => true,
                        'created_at' => $enquiryDate,
                        'updated_at' => $enquiryDate,
                    ]
                );

                EnquiryAddress::firstOrCreate(
                    ['enquiry_id' => $enquiry->id],
                    [
                        'contact_firstname' => $cust->contact_firstname,
                        'contact_lastname' => $cust->contact_lastname,
                        'contact_phone' => $cust->contact_phone,
                        'contact_email' => $cust->contact_email,
                        'address' => $loc['addr'],
                        'county' => $loc['county'],
                        'postcode' => $loc['postcode'],
                        'country' => 'United Kingdom',
                    ]
                );

                EnquiryNote::firstOrCreate(
                    ['enquiry_id' => $enquiry->id, 'note_by' => $admin->id],
                    [
                        'note' => "Initial tender questionnaire acknowledged. Estimated budget range £" . number_format(rand(45, 450) * 1000) . ". Site survey scheduled with project lead.",
                        'note_type' => 'general',
                        'note_status' => 'active',
                    ]
                );
            }

            // 9. Generate 100 Pipeline Leads
            $leadStatuses = ['new', 'new', 'inprogress', 'inprogress', 'on_hold', 'completed'];

            for ($i = 1; $i <= 100; $i++) {
                $cust = $customerPool[($i + 5) % count($customerPool)];
                $scope = $enquiryScopes[($i + 3) % count($enquiryScopes)];
                $priority = $enquiryPriorities[($i + 1) % count($enquiryPriorities)];
                $status = $leadStatuses[$i % count($leadStatuses)];
                $loc = $ukPostcodes[($i + 2) % count($ukPostcodes)];

                $turnaround1 = 1 + (($i * 3) % 5);
                $turnaround2 = 3 + (($i * 7) % 9);
                $totalCycle = $turnaround1 + $turnaround2;
                $baseDaysAgo = 5 + $totalCycle + (int)(($i / 100.0) * 75);
                $enquiryDate = Carbon::now()->subDays($baseDaysAgo)->subHours(($i * 7) % 23);
                $leadDate = (clone $enquiryDate)->addDays($turnaround1)->addHours(($i * 11) % 19);

                $lead = Lead::firstOrCreate(
                    ['lead_name' => "LEAD-2026-" . str_pad($i, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'enquiry_id' => $i,
                        'enquiry' => "Qualified procurement lead: {$scope} for {$cust->company_name}.",
                        'lead_title' => "Commercial Opportunity - {$cust->company_name}",
                        'lead_description' => "Detailed bill of quantities being compiled. Client has confirmed landlord approval and requested comprehensive CDM 2015 safety plan.",
                        'customer_id' => $cust->id,
                        'lead_type' => 'commercial',
                        'lead_category' => 'contracting',
                        'lead_priority' => $priority,
                        'lead_status' => $status,
                        'annual_maintenance' => ($i % 2 === 0),
                        'installations' => true,
                        'repairs' => ($i % 5 === 0),
                        'testing' => true,
                        'assigned_to' => [$admin->id],
                        'created_at' => $leadDate,
                        'updated_at' => $leadDate,
                    ]
                );

                LeadAddress::firstOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'contact_firstname' => $cust->contact_firstname,
                        'contact_lastname' => $cust->contact_lastname,
                        'contact_phone' => $cust->contact_phone,
                        'contact_email' => $cust->contact_email,
                        'address' => $loc['addr'],
                        'county' => $loc['county'],
                        'postcode' => $loc['postcode'],
                        'country' => 'United Kingdom',
                    ]
                );

                LeadNote::firstOrCreate(
                    ['lead_id' => $lead->id, 'note_by' => $admin->id],
                    [
                        'note' => "Pricing matrix submitted for client commercial review. JCT Design & Build 2016 contract format accepted.",
                        'note_type' => 'commercial',
                        'note_status' => 'active',
                    ]
                );
            }

            // 10. Generate 100 Commercial Jobs (Fjob)
            $jobTitles = [
                'Commercial CAT B Office Fitout & Executive Suites',
                'Mechanical HVAC Chilled Water & Heat Recovery Upgrade',
                'Commercial Electrical Distribution & Sub-Station Infrastructure',
                'Architectural Structural Glazing & Atrium Rooflight Package',
                'High-End Residential Mixed-Use Block M&E Fitout',
                'Industrial Warehouse Floor Slab, Racking & LED Lighting Package',
                'Hospitality & Luxury Retail Unit Shell & Core Fitout',
                'Educational Science Facility Cleanrooms & Fume Extraction',
                'Data Centre Uninterruptible Power Supply & Riser Containment',
                'Commercial Roofing Standing Seam & Single-Ply Membrane'
            ];

            $jobTypes = ['commercial', 'm-e', 'residential', 'civil', 'roofing', 'facilities'];
            $jobPriorities = ['urgent', 'high', 'medium', 'low'];
            $jobStatuses = ['inprogress', 'inprogress', 'accepted', 'new', 'on_hold', 'completed'];

            $jobPool = [];
            // Preserve jobs 1 & 2 if they already exist
            $existingJobCount = Fjob::count();

            for ($i = 1; $i <= 100; $i++) {
                $cust = $customerPool[($i - 1) % count($customerPool)];
                $title = $jobTitles[$i % count($jobTitles)];
                $jtype = $jobTypes[$i % count($jobTypes)];
                $jpriority = $jobPriorities[$i % count($jobPriorities)];
                $jstatus = $jobStatuses[$i % count($jobStatuses)];
                $loc = $ukPostcodes[$i % count($ukPostcodes)];

                $assignedUser = $userPool[$i % count($userPool)];
                $slug = Str::slug("job-{$i}-" . substr($cust->company_name, 0, 15) . "-project");

                $turnaround1 = 1 + (($i * 3) % 5);
                $turnaround2 = 3 + (($i * 7) % 9);
                $totalCycle = $turnaround1 + $turnaround2;
                $baseDaysAgo = 5 + $totalCycle + (int)(($i / 100.0) * 75);
                $enquiryDate = Carbon::now()->subDays($baseDaysAgo)->subHours(($i * 7) % 23);
                $leadDate = (clone $enquiryDate)->addDays($turnaround1)->addHours(($i * 11) % 19);
                $jobDate = (clone $leadDate)->addDays($turnaround2)->addHours(($i * 5) % 17);

                $job = Fjob::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'business_id' => $business->id,
                        'customer_id' => $cust->id,
                        'enquiry_id' => $i,
                        'lead_id' => $i,
                        'job_name' => "{$cust->company_name} - Phase {$i}",
                        'job_title' => $title,
                        'job_description' => "Complete turnkey execution of {$title} in full compliance with UK Building Regulations, CDM 2015, and JCT Standard Building Contract conditions.",
                        'job_type' => $jtype,
                        'job_priority' => $jpriority,
                        'job_status' => $jstatus,
                        'annual_maintenance' => ($i % 3 === 0),
                        'installations' => true,
                        'repairs' => ($i % 4 === 0),
                        'testing' => true,
                        'assigned_to' => [$assignedUser->id, $admin->id],
                        'created_at' => $jobDate,
                        'updated_at' => $jobDate,
                    ]
                );

                $contractSum = rand(45, 850) * 1000;

                FjobQuotation::firstOrCreate(
                    ['fjob_id' => $job->id],
                    [
                        'quotation_id' => 100 + $i,
                        'quotation_number' => "QT-2026-" . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                        'quotation_date' => Carbon::now()->subMonths(3)->toDateString(),
                        'valid_until' => Carbon::now()->addMonths(6)->toDateString(),
                        'total_amount' => $contractSum,
                        'status' => 'accepted',
                    ]
                );

                FjobAddress::firstOrCreate(
                    ['fjob_id' => $job->id],
                    [
                        'contact_firstname' => $cust->contact_firstname,
                        'contact_lastname' => $cust->contact_lastname,
                        'contact_phone' => $cust->contact_phone,
                        'contact_email' => $cust->contact_email,
                        'address' => $loc['addr'],
                        'county' => $loc['county'],
                        'postcode' => $loc['postcode'],
                        'country' => 'United Kingdom',
                    ]
                );

                FjobNote::firstOrCreate(
                    ['fjob_id' => $job->id, 'note_by' => $assignedUser->id],
                    [
                        'note' => "Site works progressing according to master construction programme Rev C. RAMS approved by principal contractor.",
                        'note_type' => 'progress',
                        'note_status' => 'active',
                    ]
                );

                $jobPool[] = $job;
            }

            // 11. Generate 100 Sales Invoices (Compliant with UK VAT & CIS)
            $invStatuses = ['paid', 'paid', 'pending', 'overdue', 'draft'];

            for ($i = 1; $i <= 100; $i++) {
                $job = $jobPool[($i - 1) % count($jobPool)];
                $cust = Customer::find($job->customer_id);
                $status = $invStatuses[$i % count($invStatuses)];

                $daysAgo = rand(5, 75);
                $invDate = Carbon::now()->subDays($daysAgo);
                $dueDate = $invDate->copy()->addDays(30);

                $subtotal = rand(8, 95) * 1000;
                $taxAmount = round($subtotal * 0.20, 2);
                $totalAmount = $subtotal + $taxAmount;

                // 20% CIS deduction on labor portion (assume labor is 60% of subtotal)
                $isCis = ($i % 2 === 0);
                $cisDeduction = 0;
                if ($isCis) {
                    $laborPortion = $subtotal * 0.60;
                    $cisDeduction = round($laborPortion * 0.20, 2);
                }
                $netPayable = $totalAmount - $cisDeduction;

                $invoice = Invoice::firstOrCreate(
                    ['invoice_number' => "INV-2026-" . str_pad($i, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'customer_id' => $cust->id,
                        'job_id' => $job->id,
                        'quotation_id' => 100 + $job->id,
                        'invoice_date' => $invDate->toDateString(),
                        'due_date' => $dueDate->toDateString(),
                        'subtotal' => $subtotal,
                        'tax_rate' => 20.00,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'cis_applicable' => $isCis,
                        'cis_deduction_rate' => $isCis ? 20.00 : 0.00,
                        'cis_deduction_amount' => $cisDeduction,
                        'net_payable' => $netPayable,
                        'status' => $status,
                        'payment_terms' => '30 Days Net',
                        'notes' => 'Valuation interim payment application certified by client contract administrator.',
                        'paid_at' => ($status === 'paid') ? $dueDate->copy()->subDays(rand(1, 10)) : null,
                        'payment_reference' => ($status === 'paid') ? ('BACS-' . strtoupper(Str::random(10))) : null,
                    ]
                );

                InvoiceItem::firstOrCreate(
                    ['invoice_id' => $invoice->id, 'description' => 'Commercial Construction Fitout - Materials & Staging'],
                    [
                        'quantity' => 1,
                        'unit_price' => round($subtotal * 0.40, 2),
                        'tax_rate' => 20.00,
                        'total_price' => round($subtotal * 0.40, 2),
                        'is_labor' => false,
                    ]
                );

                InvoiceItem::firstOrCreate(
                    ['invoice_id' => $invoice->id, 'description' => 'Direct Site Labor, Installation & Engineering Verification'],
                    [
                        'quantity' => 1,
                        'unit_price' => round($subtotal * 0.60, 2),
                        'tax_rate' => 20.00,
                        'total_price' => round($subtotal * 0.60, 2),
                        'is_labor' => true,
                    ]
                );
            }

            // 12. Generate 100 Procurement Purchase Orders, Deliveries & Supplier Bills
            $poStatuses = ['received', 'received', 'approved', 'draft'];

            for ($i = 1; $i <= 100; $i++) {
                $sup = $supplierPool[$i % count($supplierPool)];
                $job = $jobPool[$i % count($jobPool)];
                $wh = $warehousePool[$i % count($warehousePool)];
                $status = $poStatuses[$i % count($poStatuses)];

                $orderDate = Carbon::now()->subDays(rand(10, 45));
                $delivDate = $orderDate->copy()->addDays(5);

                $item1Qty = rand(50, 300);
                $item1Price = rand(15, 65);
                $item2Qty = rand(10, 80);
                $item2Price = rand(45, 180);

                $subtotal = ($item1Qty * $item1Price) + ($item2Qty * $item2Price);
                $taxAmount = round($subtotal * 0.20, 2);
                $totalAmount = $subtotal + $taxAmount;

                $po = PurchaseOrder::firstOrCreate(
                    ['po_number' => "PO-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'supplier_id' => $sup->id,
                        'job_id' => $job->id,
                        'warehouse_id' => $wh->id,
                        'order_date' => $orderDate->toDateString(),
                        'expected_delivery_date' => $delivDate->toDateString(),
                        'status' => $status,
                        'tax_rate' => 20.00,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'notes' => "Site offload contact: Site Manager 30 mins prior to arrival.",
                        'created_by' => $admin->id,
                    ]
                );

                $poItem1 = PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'description' => $stockItemPool[($i * 2) % count($stockItemPool)]->name],
                    [
                        'quantity' => $item1Qty,
                        'received_quantity' => ($status === 'received') ? $item1Qty : 0,
                        'unit_price' => $item1Price,
                        'total_price' => $item1Qty * $item1Price,
                    ]
                );

                $poItem2 = PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'description' => $stockItemPool[($i * 2 + 1) % count($stockItemPool)]->name],
                    [
                        'quantity' => $item2Qty,
                        'received_quantity' => ($status === 'received') ? $item2Qty : 0,
                        'unit_price' => $item2Price,
                        'total_price' => $item2Qty * $item2Price,
                    ]
                );

                // If received, create GRN and 3-Way Matched Supplier Bill
                if ($status === 'received') {
                    $grn = GoodsReceivedNote::firstOrCreate(
                        ['grn_number' => "GRN-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                        [
                            'business_id' => $business->id,
                            'purchase_order_id' => $po->id,
                            'delivery_note_number' => "DN-" . strtoupper(Str::random(8)),
                            'received_date' => $delivDate->toDateString(),
                            'received_by' => $admin->id,
                            'notes' => 'Materials inspected on site, offloaded and stored in secure dry compound.',
                        ]
                    );

                    GoodsReceivedNoteItem::firstOrCreate(
                        ['goods_received_note_id' => $grn->id, 'purchase_order_item_id' => $poItem1->id],
                        ['quantity_received' => $item1Qty, 'condition' => 'good']
                    );

                    GoodsReceivedNoteItem::firstOrCreate(
                        ['goods_received_note_id' => $grn->id, 'purchase_order_item_id' => $poItem2->id],
                        ['quantity_received' => $item2Qty, 'condition' => 'good']
                    );

                    $hasVariance = ($i % 5 === 0);
                    $matchStatus = $hasVariance ? 'variance_hold' : 'approved';
                    $billTotal = $hasVariance ? ($totalAmount * 1.15) : $totalAmount;

                    $bill = SupplierBill::firstOrCreate(
                        ['bill_number' => "BILL-" . substr(strtoupper(Str::slug($sup->name)), 0, 4) . "-" . ($i + 10)],
                        [
                            'business_id' => $business->id,
                            'internal_reference' => "INT-BILL-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT),
                            'purchase_order_id' => $po->id,
                            'supplier_id' => $sup->id,
                            'job_id' => $job->id,
                            'bill_date' => $delivDate->copy()->addDays(2)->toDateString(),
                            'due_date' => $delivDate->copy()->addDays(32)->toDateString(),
                            'subtotal' => $hasVariance ? ($subtotal * 1.15) : $subtotal,
                            'tax_amount' => round(($hasVariance ? ($subtotal * 1.15) : $subtotal) * 0.20, 2),
                            'total_amount' => $billTotal,
                            'match_status' => $matchStatus,
                            'approval_status' => $hasVariance ? 'pending' : 'approved',
                            'approved_by' => $hasVariance ? null : $admin->id,
                            'approved_at' => $hasVariance ? null : Carbon::now()->subDays(5),
                            'variance_notes' => $hasVariance ? "Price variance surge of +15% over PO contractual agreement. On commercial hold." : null,
                        ]
                    );

                    SupplierBillItem::firstOrCreate(
                        ['supplier_bill_id' => $bill->id, 'purchase_order_item_id' => $poItem1->id],
                        [
                            'description' => $poItem1->description,
                            'billed_quantity' => $item1Qty,
                            'billed_unit_price' => $item1Price,
                            'billed_total' => $item1Qty * $item1Price,
                            'po_quantity' => $item1Qty,
                            'po_unit_price' => $item1Price,
                            'grn_received_quantity' => $item1Qty,
                            'quantity_variance' => 0.00,
                            'price_variance_amount' => 0.00,
                            'price_variance_percentage' => 0.00,
                            'variance_type' => 'none',
                        ]
                    );
                }
            }

            // 13. Generate 100 Burdened Operative Timesheets
            $timesheetStatuses = ['approved', 'approved', 'submitted'];

            for ($i = 1; $i <= 100; $i++) {
                $worker = $userPool[$i % count($userPool)];
                $job = $jobPool[$i % count($jobPool)];
                $status = $timesheetStatuses[$i % count($timesheetStatuses)];

                $workDate = Carbon::now()->subDays(rand(1, 60));
                $stdHours = 37.5;
                $ot1Hours = ($i % 3 === 0) ? 4.0 : 0.0;
                $ot2Hours = ($i % 6 === 0) ? 2.0 : 0.0;
                $travelHours = ($i % 2 === 0) ? 2.5 : 1.0;
                $totalHours = $stdHours + $ot1Hours + $ot2Hours + $travelHours;

                $rateCard = LaborRateCard::where('user_id', $worker->id)->first();
                $baseRate = $rateCard ? $rateCard->base_hourly_rate : 25.00;

                $directPay = ($stdHours * $baseRate) + ($ot1Hours * $baseRate * 1.5) + ($ot2Hours * $baseRate * 2.0) + ($travelHours * $baseRate);
                $burdenMultiplier = 1.2680; // 26.8% NI, pension, overheads
                $fullyBurdenedCost = round($directPay * $burdenMultiplier, 2);

                LaborTimesheet::firstOrCreate(
                    ['timesheet_number' => "TS-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'user_id' => $worker->id,
                        'job_id' => $job->id,
                        'work_date' => $workDate->toDateString(),
                        'travel_hours' => $travelHours,
                        'standard_hours' => $stdHours,
                        'overtime_1_5_hours' => $ot1Hours,
                        'overtime_2_0_hours' => $ot2Hours,
                        'total_hours' => $totalHours,
                        'cost_rate_per_hour' => $baseRate,
                        'burden_multiplier' => $burdenMultiplier,
                        'direct_pay_amount' => $directPay,
                        'fully_burdened_cost' => $fullyBurdenedCost,
                        'status' => $status,
                        'approved_by' => ($status === 'approved') ? $admin->id : null,
                        'approved_at' => ($status === 'approved') ? $workDate->copy()->addDays(2) : null,
                        'notes' => 'Weekly operative timesheet allocated to project code.',
                    ]
                );
            }

            // 14. Generate 100 Plant & Equipment Hires
            $plantEquipments = [
                ['name' => '14m Electric Scissor Lift (Dingli JCPT1412)', 'cat' => 'mewp', 'daily' => 85.00, 'weekly' => 340.00],
                ['name' => '20m Articulated Diesel Boom Lift (Genie Z-60)', 'cat' => 'mewp', 'daily' => 160.00, 'weekly' => 640.00],
                ['name' => '1.5T Mini Excavator (Kubota KX015-4)', 'cat' => 'excavator', 'daily' => 120.00, 'weekly' => 480.00],
                ['name' => '8T Midi Excavator Zero Tail Swing (JCB 85Z-2)', 'cat' => 'excavator', 'daily' => 220.00, 'weekly' => 880.00],
                ['name' => '8kVA Super-Silenced Mobile Generator', 'cat' => 'generator', 'daily' => 50.00, 'weekly' => 200.00],
                ['name' => '60kVA 3-Phase Diesel Generator (Pramac GSW65)', 'cat' => 'generator', 'daily' => 140.00, 'weekly' => 560.00],
                ['name' => 'VT1 Eco 9m Hydraulic Mast Mobile Tower Light', 'cat' => 'lighting', 'daily' => 45.00, 'weekly' => 180.00],
                ['name' => '17m Construction Telehandler (JCB 540-170)', 'cat' => 'telehandler', 'daily' => 240.00, 'weekly' => 960.00],
                ['name' => '6T Forward Tipping Site Dumper (Thwaites)', 'cat' => 'dumper', 'daily' => 110.00, 'weekly' => 440.00],
                ['name' => '120 Heavy Duty Vibrating Trench Roller (Bomag)', 'cat' => 'compactor', 'daily' => 95.00, 'weekly' => 380.00],
            ];

            $plantStatuses = ['active', 'active', 'overdue', 'off_hired'];

            for ($i = 1; $i <= 100; $i++) {
                $equip = $plantEquipments[$i % count($plantEquipments)];
                $job = $jobPool[$i % count($jobPool)];
                $sup = $supplierPool[1]; // Sunbelt rentals or specialist hire
                $status = $plantStatuses[$i % count($plantStatuses)];

                $startDaysAgo = rand(10, 40);
                $startDate = Carbon::now()->subDays($startDaysAgo);

                if ($status === 'overdue') {
                    $expectedEndDate = Carbon::now()->subDays(rand(2, 6)); // Expired in the past!
                    $actualEndDate = null;
                } elseif ($status === 'off_hired') {
                    $expectedEndDate = Carbon::now()->subDays(rand(3, 8));
                    $actualEndDate = $expectedEndDate;
                } else {
                    $expectedEndDate = Carbon::now()->addDays(rand(5, 20));
                    $actualEndDate = null;
                }

                $accrued = rand(4, 18) * $equip['daily'];

                PlantHire::firstOrCreate(
                    ['hire_reference' => "PH-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'job_id' => $job->id,
                        'supplier_id' => $sup->id,
                        'item_name' => $equip['name'],
                        'category' => $equip['cat'],
                        'serial_asset_tag' => "SN-" . strtoupper(substr($equip['cat'], 0, 3)) . "-" . rand(1000, 9999),
                        'start_date' => $startDate->toDateString(),
                        'expected_end_date' => $expectedEndDate->toDateString(),
                        'actual_end_date' => $actualEndDate ? $actualEndDate->toDateString() : null,
                        'daily_rate' => $equip['daily'],
                        'weekly_rate' => $equip['weekly'],
                        'delivery_charge' => 120.00,
                        'collection_charge' => 120.00,
                        'status' => $status,
                        'accrued_hire_cost' => $accrued,
                        'off_hire_reference' => ($status === 'off_hired') ? ('OFF-HIRE-SB-' . rand(10000, 99999)) : null,
                        'off_hired_at' => ($status === 'off_hired') ? $actualEndDate : null,
                        'off_hired_by' => ($status === 'off_hired') ? $admin->id : null,
                        'notes' => ($status === 'overdue') ? "ALERT: Plant hire period expired. Off-hire immediately to halt standby bleed cost!" : "Certified machine with LOLER certificate in cab.",
                    ]
                );
            }

            // 15. Generate 25 Commercial Variations (Change Orders)
            for ($i = 1; $i <= 25; $i++) {
                $job = $jobPool[$i % count($jobPool)];
                $cost = rand(3, 35) * 1000;
                $markup = 20.00;
                $quoted = round($cost * 1.20, 2);

                JobVariation::firstOrCreate(
                    ['variation_number' => "VAR-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'job_id' => $job->id,
                        'title' => "Commercial Change Order #{$i} - Client Architectural Revision",
                        'description' => "Architectural instruction revision for MEP rerouting, acoustic baffle additions, and premium partition finish specifications.",
                        'cost_amount' => $cost,
                        'markup_percentage' => $markup,
                        'quoted_amount' => $quoted,
                        'status' => ($i % 3 === 0) ? 'submitted' : 'approved',
                        'client_signer_name' => "Project Director ({$job->job_name})",
                        'client_signer_email' => "commercial@client.local",
                        'signature_data' => 'DATA_URL_ESIGN_VERIFIED',
                        'signed_at' => Carbon::now()->subDays(rand(2, 20)),
                        'approved_by' => $admin->id,
                        'approved_at' => Carbon::now()->subDays(rand(2, 20)),
                    ]
                );
            }

            // 16. Generate 25 Valuations & Retentions
            for ($i = 1; $i <= 25; $i++) {
                $job = $jobPool[$i % count($jobPool)];
                $gross = rand(40, 220) * 1000;
                $retention = round($gross * 0.05, 2); // 5% retention
                $net = $gross - $retention;

                $val = JobValuation::firstOrCreate(
                    ['application_number' => "APP-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'job_id' => $job->id,
                        'valuation_date' => Carbon::now()->subDays(rand(5, 30))->toDateString(),
                        'period_end' => Carbon::now()->subDays(rand(5, 30))->toDateString(),
                        'cumulative_work_value' => $gross * 0.90,
                        'materials_on_site_value' => $gross * 0.10,
                        'gross_valuation' => $gross,
                        'previous_certified_amount' => 0.00,
                        'current_gross_amount' => $gross,
                        'retention_rate' => 5.00,
                        'retention_deduction' => $retention,
                        'net_claim_amount' => $net,
                        'certified_amount' => $net,
                        'status' => ($i % 2 === 0) ? 'paid' : 'certified',
                        'certified_at' => Carbon::now()->subDays(rand(2, 10)),
                        'certified_by' => $admin->id,
                        'paid_at' => ($i % 2 === 0) ? Carbon::now()->subDays(2)->toDateString() : null,
                        'notes' => 'Certified under JCT Standard Building Contract 2016 interim certificate.',
                    ]
                );

                JobRetention::firstOrCreate(
                    ['valuation_id' => $val->id],
                    [
                        'business_id' => $business->id,
                        'job_id' => $job->id,
                        'retention_reference' => "RET-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT),
                        'total_retained_amount' => $retention,
                        'practical_completion_date' => Carbon::now()->addMonths(6)->toDateString(),
                        'pc_release_amount' => round($retention * 0.50, 2),
                        'defects_expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                        'defects_release_amount' => round($retention * 0.50, 2),
                        'status' => 'held',
                    ]
                );
            }

            // 17. Subcontractor Payment Certificates (15 certificates)
            for ($i = 1; $i <= 15; $i++) {
                $sub = $subPool[$i % count($subPool)];
                $job = $jobPool[$i % count($jobPool)];
                $gross = rand(12, 45) * 1000;
                $materials = round($gross * 0.25, 2);
                $labor = $gross - $materials;

                $cisRate = ($sub->cis_tax_status === 'higher') ? 30.00 : (($sub->cis_tax_status === 'gross') ? 0.00 : 20.00);
                $cisDeduction = round($labor * ($cisRate / 100), 2);
                $netPayable = $gross - $cisDeduction;

                SubcontractorPaymentCertificate::firstOrCreate(
                    ['certificate_number' => "SPC-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'subcontractor_id' => $sub->id,
                        'job_id' => $job->id,
                        'period_start' => Carbon::now()->subDays(28)->toDateString(),
                        'period_end' => Carbon::now()->toDateString(),
                        'tax_month' => Carbon::now()->format('Y-m'),
                        'gross_amount' => $gross,
                        'materials_cost' => $materials,
                        'labor_amount' => $labor,
                        'cis_tax_rate' => $cisRate,
                        'cis_deduction_amount' => $cisDeduction,
                        'net_payable' => $netPayable,
                        'payment_status' => ($i % 3 === 0) ? 'draft' : 'certified',
                        'certified_by' => ($i % 3 === 0) ? null : $admin->id,
                        'certified_at' => ($i % 3 === 0) ? null : Carbon::now()->subDays(rand(1, 5)),
                        'notes' => "Interim valuation certificate for {$sub->trade_specialism}.",
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
