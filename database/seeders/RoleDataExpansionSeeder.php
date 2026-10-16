<?php

namespace Database\Seeders;

use App\Models\BusinessInfo;
use App\Models\CourseCertificate;
use App\Models\DriverDetail;
use App\Models\Fjob;
use App\Models\FjobHsCheck;
use App\Models\FjobProgressTask;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\HsChecklist;
use App\Models\HsChecklistAttribute;
use App\Models\HsTool;
use App\Models\HsToolCheckup;
use App\Models\JobRetention;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\PurchaseOrder;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockItemRequisition;
use App\Models\StockItemRequisitionDetail;
use App\Models\StockItemVariant;
use App\Models\StockWarehouse;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\User;
use App\Models\VehicleCategory;
use App\Models\VehicleCheckCategory;
use App\Models\VehicleCheckChecklist;
use App\Models\VehicleDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleDataExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $business = BusinessInfo::first();
        if (!$business) {
            return;
        }

        $admin = User::where('email', 'admin@neoerp.local')->first() ?? User::first();
        $chloe = User::where('email', 'chloe.bennett@neoerp.local')->first() ?? $admin;
        $sarah = User::where('email', 'sarah.engineer@neoerp.local')->first() ?? $admin;
        $james = User::where('email', 'james.oconnor@neoerp.local')->first() ?? $admin;
        $michael = User::where('email', 'michael.chang@neoerp.local')->first() ?? $admin;
        $priya = User::where('email', 'priya.patel@neoerp.local')->first() ?? $admin;
        $marcus = User::where('email', 'marcus.vance@neoerp.local')->first() ?? $admin;

        $jobs = Fjob::all();
        if ($jobs->isEmpty()) {
            return;
        }

        $operatives = User::whereIn('email', [
            'dave.operative@neoerp.local',
            'liam.wright@neoerp.local',
            'elena.rostova@neoerp.local',
            'tom.hardy@neoerp.local',
            'james.oconnor@neoerp.local',
            'sarah.engineer@neoerp.local',
        ])->get();
        if ($operatives->isEmpty()) {
            $operatives = User::all();
        }

        // =========================================================================
        // 1. POPULATE 100 COURSE CERTIFICATES (H&S Compliance Auditor Chloe Bennett)
        // =========================================================================
        $certTemplates = [
            ['SMSTS - Site Management Safety Training Scheme', 'CITB mandatory 5-day certification for site managers covering CDM regulations, risk assessments, scaffolding, excavations, and high-risk operations.', 'CITB ConstructionSkills', '5 Years'],
            ['SSSTS - Site Supervision Safety Training Scheme', 'CITB 2-day qualification for first-line supervisors and team leaders on health, safety, and occupational welfare control on site.', 'CITB ConstructionSkills', '5 Years'],
            ['CSCS Black Manager Card - Construction Management', 'Platinum-tier NVQ Level 6/7 construction management qualification verifying leadership competence in structural engineering and CDM 2015.', 'CSCS UK', '5 Years'],
            ['CSCS Gold Supervisor Card - Advanced Craft / Supervisor', 'NVQ Level 3/4 supervisory credential verifying trade craft mastery and site health and safety leadership.', 'CSCS UK', '5 Years'],
            ['CSCS Blue Skilled Worker - Carpentry & Architectural Joinery', 'Level 2 NVQ craft certification for structural timber, drylining partitions, roof trusses, and architectural fit-out.', 'CSCS UK', '5 Years'],
            ['CSCS Blue Skilled Worker - Commercial Electrical Installation', 'Gold standard electrotechnical certification aligned with 18th Edition BS 7671 wiring regulations and ECS accreditation.', 'JIB / ECS', '5 Years'],
            ['CSCS Blue Skilled Worker - Commercial Roofing & Waterproofing', 'NVQ Level 2 in Single Ply Waterproofing and High-Performance Liquid Roofing Membranes.', 'CSCS UK', '5 Years'],
            ['IPAF 3a & 3b Mobile Elevating Work Platforms (MEWP)', 'International Powered Access Federation certification for mobile vertical scissor lifts and mobile self-propelled boom lifts.', 'IPAF International', '5 Years'],
            ['PASMA Towers for Users - Mobile Access Towers', 'National standard qualification for assembling, altering, inspecting, moving, and dismantling alloy mobile scaffolding towers.', 'PASMA', '5 Years'],
            ['Level 3 Award in First Aid at Work (FAAW)', 'HSE-compliant 3-day emergency medical response qualification covering CPR, defibrillators (AED), fractures, major bleeds, and trauma care.', 'St John Ambulance', '3 Years'],
            ['Emergency First Aid at Work (EFAW) + Defibrillator (AED)', 'HSE-recognized 1-day rapid workplace emergency first aid qualification for active construction environments.', 'British Red Cross', '3 Years'],
            ['Mental Health First Aid in Construction (MHFA)', 'Nationally accredited training to recognize mental ill-health, offer immediate support, and implement wellbeing interventions on site.', 'MHFA England', '3 Years'],
            ['UKATA Asbestos Awareness (Category A)', 'Mandatory UKATA annual training covering asbestos hazard identification, ACM types, health risks, and emergency discovery protocols.', 'UKATA Registered', '1 Year'],
            ['UKATA Non-Licensable Asbestos Work (Category B)', 'Practical training for drilling, minor removal of asbestos cement, textured coatings, and vinyl floor tiles under controlled conditions.', 'UKATA Registered', '1 Year'],
            ['Working at Height & Full Body Harness Inspection', 'Competent person certification for pre-use inspection of fall arrest lanyards, inertia reels, scaffolding anchor points, and rescue kits.', 'Height Safety Assoc', '2 Years'],
            ['Confined Space Entry with Escape Breathing Apparatus', 'City & Guilds 6150 Level 2 medium-risk confined space certification covering gas monitors, tripods, winches, and escape BA sets.', 'City & Guilds', '3 Years'],
            ['NEBOSH National Certificate in Construction H&S', 'Gold-standard international safety credential assessing legal requirements, structural hazards, fire, demolition, and plant safety.', 'NEBOSH', 'Permanent'],
            ['IOSH Managing Safely in Construction', 'Chartered Institution of Occupational Safety and Health practical certificate for proactive risk mitigation and safety culture.', 'IOSH UK', '3 Years'],
            ['CPCS A17 Telescopic Handler (All Sizes inc 360 Slew)', 'Construction Plant Competence Scheme telehandler license covering load charts, pallet forks, winches, and rough terrain operations.', 'CPCS UK', '5 Years'],
            ['CPCS A09 Forward Tipping Dumper (Tracked & Wheeled)', 'Site dumper operator accreditation covering steep gradients, trench spoil tipping, and exclusion zone traffic management.', 'CPCS UK', '5 Years'],
            ['CPCS A59 360 Excavator Above 10 Tonnes', 'Heavy tracked excavator license covering deep foundations, battering slopes, trench box handling, and quick-hitch safety.', 'CPCS UK', '5 Years'],
            ['CPCS A40 Slinger / Signaller (All Duties)', 'Banksperson accreditation for crane sling angles, synthetic webbing and chain sling inspection, and blind-lift hand/radio signals.', 'CPCS UK', '5 Years'],
            ['CPCS A61 Appointed Person - Lifting Operations', 'BS 7121 compliant lift planner credential producing comprehensive lift studies, ground pressure calculations, and crane selection.', 'CPCS UK', '5 Years'],
            ['Abrasive Wheels Safety & Angle Grinder Mounting', 'Statutory PUWER regulation training for inspecting, dressing, and mounting diamond blades and cutting discs on 110V site saws.', 'BSIF British Safety', '2 Years'],
            ['Cable Avoidance Tool (CAT & Genny) Operator', 'EUSR utility scanning certification for detecting live underground power cables, gas mains, and fibre optic ducting prior to excavation.', 'EUSR Certified', '3 Years'],
            ['Temporary Works Coordinator (TWC) BS 5975', 'CITB certified coordinator managing temporary works design briefs, load checks, formwork striking, and design certificates.', 'CITB ConstructionSkills', '5 Years'],
            ['Temporary Works Supervisor (TWS)', 'Site supervisory qualification verifying falsework erection, trench shoring inspections, and permit-to-load compliance.', 'CITB ConstructionSkills', '5 Years'],
            ['NICEIC 18th Edition Wiring Regulations BS 7671:2018+A2', 'Statutory electrical compliance qualification governing electrical installation safety, protection against fire, and RCD testing.', 'NICEIC / City & Guilds', '5 Years'],
            ['Fire Warden & Hot Works Permit Marshal', 'Designated fire safety officer certification covering hot works permit compliance, 60-minute fire watch, and site evacuation.', 'Fire Protection Assoc', '2 Years'],
            ['Environmental Management & Spill Kit First Responder', 'IEMA endorsed environmental training for hazardous chemical containment, silt prevention, and drainage protection.', 'IEMA UK', '2 Years'],
        ];

        for ($i = 1; $i <= 100; $i++) {
            $tpl = $certTemplates[($i - 1) % count($certTemplates)];
            $code = str_pad($i, 3, '0', STR_PAD_LEFT);
            $suffix = ($i > count($certTemplates)) ? " (Ref: NC-{$code})" : "";
            $status = ($i % 15 === 0) ? 'expired' : (($i % 8 === 0) ? 'pending' : 'active');

            CourseCertificate::firstOrCreate(
                ['certificate_name' => "{$tpl[0]}{$suffix}"],
                [
                    'description' => "{$tpl[1]} Registered accreditation code: UK-CERT-{$code}. Full syllabus audit verified.",
                    'authorized_by' => $tpl[2],
                    'validity_period' => $tpl[3],
                    'status' => $status,
                ]
            );
        }

        // =========================================================================
        // 2. POPULATE 100 VEHICLES (Fleet & Compliance)
        // =========================================================================
        $vehicleCategories = VehicleCategory::all();
        if ($vehicleCategories->isEmpty()) {
            $vanCat = VehicleCategory::create(['category_name' => 'Commercial Vans', 'description' => '3.5t Panel Vans & Transits', 'is_active' => true]);
            $truckCat = VehicleCategory::create(['category_name' => 'Tipper & Dropside Trucks', 'description' => '7.5t & Dropside Fleet', 'is_active' => true]);
            $pickupCat = VehicleCategory::create(['category_name' => '4x4 Site Pickups', 'description' => 'All-Terrain 4x4 Support', 'is_active' => true]);
            $vehicleCategories = collect([$vanCat, $truckCat, $pickupCat]);
        }

        $vehicleModels = [
            ['Ford', 'Transit Custom 320 L2H1', '2.0 EcoBlue 130PS', 'Commercial Vans'],
            ['Mercedes-Benz', 'Sprinter 315 CDI L3H2', '2.0 CDI RWD 150PS', 'Commercial Vans'],
            ['Toyota', 'Hilux Invincible X 4x4', '2.8 D-4D 204PS', '4x4 Site Pickups'],
            ['Ford', 'Ranger Wildtrak Double Cab', '2.0 EcoBlue Bi-Turbo 205PS', '4x4 Site Pickups'],
            ['Vauxhall', 'Vivaro Pro L2H1', '2.0 Turbo D 145PS', 'Commercial Vans'],
            ['Renault', 'Master LM35 3.5t Luton Box', '2.3 dCi 145PS', 'Commercial Vans'],
            ['Iveco', 'Daily 35C16 3.5t Dropside', '3.0 HPI 160PS Twin Wheel', 'Tipper & Dropside Trucks'],
            ['DAF', 'LF 180 7.5t Insulated Tipper', '4.5 PACCAR PX-5 180PS', 'Tipper & Dropside Trucks'],
            ['Peugeot', 'Boxer Professional 335 L3H2', '2.2 BlueHDi 140PS', 'Commercial Vans'],
            ['Volkswagen', 'Crafter CR35 Commerce Pro', '2.0 TDI 140PS', 'Commercial Vans'],
            ['Isuzu', 'D-Max V-Cross 4x4', '1.9 Ddi 164PS', '4x4 Site Pickups'],
            ['Mitsubishi', 'L200 Barbarian X Double Cab', '2.2 DI-D 150PS', '4x4 Site Pickups'],
        ];

        $regPrefixes = ['LO', 'EA', 'BD', 'KM', 'WF', 'YK', 'HN', 'LK', 'BP', 'RE', 'WN', 'SK', 'GN', 'FE', 'GL'];
        for ($i = 1; $i <= 100; $i++) {
            $modelData = $vehicleModels[($i - 1) % count($vehicleModels)];
            $cat = $vehicleCategories->firstWhere('category_name', $modelData[3]) ?? $vehicleCategories->first();
            $prefix = $regPrefixes[($i - 1) % count($regPrefixes)];
            $yearTag = ($i % 2 === 0) ? '24' : (($i % 3 === 0) ? '73' : '23');
            $randomLetters = chr(65 + ($i % 26)) . chr(65 + (($i * 3) % 26)) . chr(65 + (($i * 7) % 26));
            $regNo = "{$prefix}{$yearTag} {$randomLetters}";

            $status = ($i % 12 === 0) ? 'Under Maintenance' : (($i % 4 === 0) ? 'Assigned' : 'Available');

            VehicleDetail::firstOrCreate(
                ['registration_no' => $regNo],
                [
                    'category_id' => $cat->id,
                    'manufacturer' => $modelData[0],
                    'model' => $modelData[1],
                    'engine' => $modelData[2],
                    'color' => ($i % 3 === 0) ? 'Frozen White' : (($i % 2 === 0) ? 'Magnetic Grey' : 'Agate Black'),
                    'year' => 2020 + ($i % 5),
                    'fuel_type' => ($i % 6 === 0) ? 'Electric' : 'Diesel',
                    'mileage' => 12500 + ($i * 680),
                    'status' => $status,
                    'mot_expiry_date' => Carbon::now()->addMonths(($i % 12) + 2)->toDateString(),
                    'tax_expiry_date' => Carbon::now()->addMonths(($i % 10) + 1)->toDateString(),
                    'insurance_expiry_date' => Carbon::now()->addMonths(8)->toDateString(),
                    'next_service_date' => Carbon::now()->addMonths(($i % 8) + 1)->toDateString(),
                    'vin_number' => 'VIN' . strtoupper(Str::random(14)),
                ]
            );
        }

        // =========================================================================
        // 3. POPULATE 50 DRIVER PROFILES (Drivers Directory)
        // =========================================================================
        $allUsers = User::limit(50)->get();
        $licenceCats = ['Category B (Car & Van up to 3.5t)', 'Category C1 (Medium Goods 3.5t - 7.5t)', 'Category C (HGV Class 2)', 'Category CE (HGV Class 1)'];
        $idx = 1;
        $driverNames = [
            'Dave Mason', 'Liam Wright', 'Elena Rostova', 'Tom Hardy', 'Arthur Pendelton',
            'George Clarke', 'Callum MacLeod', 'Declan O\'Reilly', 'Bradley Cooper', 'Nathaniel Ward',
            'Sean Fitzpatrick', 'Kieran Hughes', 'Gareth Evans', 'Rhys Morgan', 'Alistair Campbell',
            'Duncan Stewart', 'Niall Horan', 'Ewan McGregor', 'Colin Farrell', 'Brendan Gleeson',
            'Patrick Stewart', 'Ian McKellen', 'Michael Caine', 'Kenneth Branagh', 'Gary Oldman',
            'Daniel Craig', 'Tom Hiddleston', 'Benedict Cumberbatch', 'Martin Freeman', 'Idris Elba',
            'Henry Cavill', 'Jude Law', 'Christian Bale', 'Robert Pattinson', 'Tom Holland',
            'Nicholas Hoult', 'Aaron Taylor-Johnson', 'Richard Madden', 'Jamie Dornan', 'Cillian Murphy',
            'Andrew Scott', 'Paul Mescal', 'Barry Keoghan', 'Jack Lowden', 'George MacKay',
            'Harris Dickinson', 'Regé-Jean Page', 'Dev Patel', 'Damson Idris', 'John Boyega',
            'Edward Norton', 'Mark Strong', 'Jason Statham', 'Hugh Grant', 'Pierce Brosnan',
            'Sean Bean', 'Christopher Eccleston', 'David Tennant', 'Matt Smith', 'Peter Capaldi',
            'Jodie Whittaker', 'David Suchet', 'Rowan Atkinson', 'Hugh Laurie', 'Stephen Fry',
            'Richard Ayoade', 'Simon Pegg', 'Nick Frost', 'Martin Clunes', 'David Jason',
            'Timothy Spall', 'Jim Broadbent', 'Bill Nighy', 'Alan Rickman', 'Ralph Fiennes',
            'Jeremy Irons', 'Ben Kingsley', 'Anthony Hopkins', 'Brian Cox', 'Charles Dance',
            'Mark Rylance', 'Jonathan Pryce', 'David Thewlis', 'Rhys Ifans', 'Matthew Macfadyen',
            'Dan Stevens', 'Luke Evans', 'Rory McCann', 'Iain Glen', 'Jerome Flynn',
            'Clive Owen', 'Rupert Everett', 'Colin Firth', 'Hugh Bonneville', 'Dominic West',
            'Toby Jones', 'Eddie Redmayne', 'Sam Claflin', 'Douglas Booth', 'Max Irons'
        ];

        for ($idx = 1; $idx <= count($driverNames); $idx++) {
            $dName = $driverNames[$idx - 1];
            $driverUser = User::firstOrCreate(
                ['email' => 'driver.' . Str::slug($dName) . '@neoerp.local'],
                [
                    'name' => $dName,
                    'password' => Hash::make('NeoErp!Staff2026#'),
                    'business_id' => $business->id,
                ]
            );
            $driverUser->assignRole('staff');

            $licenceNo = strtoupper(substr(str_replace(' ', '', $dName), 0, 5)) . str_pad($idx * 23, 6, '0', STR_PAD_LEFT) . 'D99';
            DriverDetail::firstOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'license_no' => $licenceNo,
                    'license_issue_date' => Carbon::now()->subYears(rand(3, 15))->toDateString(),
                    'license_expiry' => Carbon::now()->addYears(rand(2, 8))->toDateString(),
                    'license_category' => $licenceCats[$idx % count($licenceCats)],
                    'digital_tacho_card' => ($idx % 3 === 0),
                    'dbs_check_passed' => true,
                    'medical_check_passed' => true,
                    'last_medical_check_date' => Carbon::now()->subMonths(rand(2, 10))->toDateString(),
                    'next_medical_due_date' => Carbon::now()->addMonths(rand(12, 24))->toDateString(),
                ]
            );
        }

        // =========================================================================
        // 3b. POPULATE ADDITIONAL STOCK ITEMS TO REACH 100+
        // =========================================================================
        $stockCats = StockCategory::all();
        $whList = StockWarehouse::all();
        if ($stockCats->isNotEmpty()) {
            $cat0 = $stockCats->first();
            $extraItems = [
                'Knauf Moisture Panel 2.4m x 1.2m x 12.5mm', 'British Gypsum SoundBloc 15mm Board', 'Kingspan Kooltherm K107 Pitched Roof Board 100mm',
                'Celotex GA4000 High Performance PIR 50mm', 'Superglass Multi-Roll 44 Loft Insulation 100mm', 'Visqueen DPM Polyethylene Damp Proof Membrane 300mu',
                'Tyvek Supro Breathable Roofing Felt 50m x 1m', 'C24 Treated Kiln Dried Timber 47mm x 150mm x 4.8m', 'C24 Treated Structural Timber 47mm x 200mm x 6.0m',
                'OSB3 Structural Board 18mm 2440 x 1220mm', 'Marine Plywood BS 1088 18mm 2440 x 1220mm', 'HardieBacker 12mm Cement Backer Board',
                'WBP Phenolic Faced Plywood 18mm Formwork', 'Speedline Drywall Screws 25mm Coarse Thread (Box 1000)', 'Spit Tapcon Concrete Screws M6 x 50mm (Box 100)',
                'Rawlplug Heavy Duty Masonry Shield Anchors M10', 'Fischer High Performance Chemical Resin Anchor 300ml', 'Unistrut Channel Cantilever Arm 450mm HDG',
                'Galvanised Perforated Banding 20mm x 10m', 'Lindab Rainwater 100mm Half Round Gutter 3m Galvanised', 'Brett Martin 110mm Push-Fit Soil Pipe 3m Black',
                'Polypipe 110mm Underground Drainage Pipe 3m Terracotta', 'Hep2O 15mm Barrier Pipe Coil 50m', 'Speedfit 22mm Push-Fit Equal Elbow',
                'Geberit Mapress Stainless Steel Press-Fit Pipe 28mm', 'Armaflex Class O Pipe Insulation 22mm x 13mm', 'Pegler Commercial Ball Valve Full Bore 1-inch BSP',
                'Worcester Bosch Commercial System Filter 1-1/4', 'Prysmian 6491B 4mm Single Core Conduit Wire Blue 100m', 'Wiska IP66 Waterproof Combi Junction Box 308',
                'Schneider Electric 3-Phase 125A Distribution Board', 'MK Electric Masterseal Plus IP66 Double Socket', 'Ansell Prism Pro Anti-Glare Fire-Rated LED Downlight',
                'Thorlux High-Bay LED 150W Industrial Luminaire', 'Honeywell Commercial Programmable Thermostat T6R', 'Gent Vigilon Addressable Optical Smoke Sensor',
                'Apollo XP95 Manual Call Point Red Surface Mount', 'KAC IP67 Weatherproof Call Point Yellow (Gas Dump)', 'C-Tec 24V 3A Monitored Power Supply Unit Box',
                'Vent-Axia Sentinel Kinetic High Flow MVHR Unit', 'Systemair K 200 EC Circular Duct Fan 200mm', 'Trox Multi-Leaf Volume Control Damper 300x200mm',
                'Actionair Fire & Smoke Damper 400mm x 400mm', 'Swegon Gold Air Handling Unit Replacement Pocket Filter', 'Rockwool Firestop Compound 20kg Bag for Penetrations',
                'Hilti CP 606 Firestop Acrylic Sealant 310ml White', 'Nullifire SC902 Fast Track Intumescent Coating 20L', 'SikaHyflex 250 Facade High Performance Joint Sealant',
                'Geocel Trade Mate Roof & Gutter Sealant 310ml Black', 'Everbuild 501 Universal PVA Building Adhesive 5L', 'SBR Waterproof Bonding Agent & Admixture 25L',
                'Ardex K 15-HB Rapid Drying Heavy Duty Levelling Compound', 'Weberpram 440 Structural Repair Mortar 25kg', 'Ronacrete RonaBond Concrete Repair Mortar 25kg',
                'Blue Circle Mastercrete Enhanced Portland Cement 25kg', 'Building Sand Concreting Ballast Bulk Bag 850kg', 'Hanson MOT Type 1 Sub-Base Granular Aggregate 850kg',
                'Ready-Mix High-Strength C35/45 Fibre Reinforced Mortar', 'Catnic External Solid Wall Steel Lintel CN81A 1800mm', 'IG Heavy Duty Box Lintel BHD100 2400mm',
                'Birtley Galvanised Cavity Lintel CB70 2100mm', 'Ancon Stainless Steel Wall Ties Type 2 225mm (Box 250)', 'Expamet Galvanised Brick Reinforcement Coil 20m',
                'Marley Eternit Rivendale Fibre Cement Roof Slate', 'Redland 49 Concrete Interlocking Roof Tile Slate Grey', 'Sika Sarnafil G410-15EL Single Ply PVC Membrane 2m x 20m',
                'Bauder Total Roof System Mineral Cap Sheet 8m Roll', 'Icopal Universal SBS Elastomeric Torch-On Underlay', 'Tremco Vulkem 350NF/351 Trafficable Balcony Waterproofing',
                'Aco MultiDrain M100D Polymer Concrete Channel 1m', 'Alumasc Heritage Cast Aluminium Half Round Gutter 125mm', 'Swish Cellular PVC Fascia Board 16mm x 200mm x 5m White',
                'Eurocell Hollow Soffit Board 300mm x 5m Anthracite Grey', 'Forticrete Architectural Fairfaced Masonry Block 100mm', 'Wienerberger Concreate Facing Brick 65mm Red Multi (Pack 500)',
                'Ibstock Tradesman Heather Facing Brick 65mm (Pack 500)', 'Bradstone Commercial Paving Slabs 600x600x50mm Buff (Pack 20)', 'Marshalls Saxon Textured Paving 450x450x35mm Natural',
                'Keyline Granite Kerb Stone 150x300x900mm Bullnose', 'Tarmac UltiFast Heavy Duty Asphalt Cold-Lay Repair 25kg', 'Fosroc Renderoc HB25 Structural Repair Mortar 25kg',
                'Mapei Keraquick S1 Rapid-Setting Flexible Tile Adhesive 20kg', 'BAL Micromax 3 Antibacterial Wall & Floor Grout 10kg White', 'Soudal Soudafoam Gun Grade Expanding Foam 750ml',
                'Bostik Blu-Tack Commercial Pack 500g', 'Gorilla Heavy Duty Grab Adhesive 290ml Cartridge', 'Everbuild Multi-Use Wonder Wipes (Tub of 100)',
                'Stanley FatMax Retractable Knife Heavy Duty', 'Irwin Vise-Grip Curved Jaw Locking Pliers 250mm', 'DeWalt SDS-Plus Hammer Drill Bit 8mm x 160mm',
                'Makita Reciprocating Saw Blade Bi-Metal 228mm', 'Bosch Professional Angle Grinder Cutting Disc 115mm (Pack 10)', 'Ox Pro Chalk Line Reel 30m with Blue Chalk',
                'Stabila R-Type 3-Vial Spirit Level 120cm', 'Scruffs Worker Plus Multi-Pocket Holster Work Trousers', 'Apache Commercial Steel Toe Cap Safety Boots Size 10'
            ];

            foreach ($extraItems as $idx => $itemName) {
                $item = StockItem::firstOrCreate(
                    ['name' => $itemName],
                    [
                        'business_id' => $business->id,
                        'category_id' => $stockCats[$idx % $stockCats->count()]->id,
                        'description' => 'Commercial grade construction specification conforming to BS EN standards.',
                        'is_active' => true,
                    ]
                );

                if ($whList->isNotEmpty()) {
                    StockItemVariant::firstOrCreate(
                        ['stock_item_id' => $item->id, 'sku' => 'SKU-' . str_pad($idx + 100, 4, '0', STR_PAD_LEFT)],
                        [
                            'name' => 'Standard Commercial Spec',
                            'quantity' => rand(25, 450),
                            'unit' => 'pcs',
                            'avg_price' => rand(15, 120),
                            'warehouse_id' => $whList[$idx % $whList->count()]->id,
                        ]
                    );
                }
            }
        }

        // =========================================================================
        // 4. POPULATE VEHICLE CHECK CATEGORIES & CHECKLISTS
        // =========================================================================
        $vChecks = [
            'Daily Pre-Use Walkaround Inspection' => [
                'Tyre condition, tread depth (min 1.6mm) and wheel nut indicators in alignment',
                'Engine oil, coolant, brake fluid, and windscreen washer fluid levels',
                'Headlights, indicators, hazard lights, reverse warning buzzer, and beacon',
                'Braking system, handbrake hold on incline, and footbrake pedal firmness',
                'Windscreen clarity, wiper blade condition, and rear-view mirrors adjustment',
                'Cab safety: seatbelts, first aid kit, dry powder fire extinguisher, and hi-vis vest',
            ],
            'Weekly Vehicle Mechanical & Fluid Audit' => [
                'Battery terminal condition, charge indicator, and alternator belt tension',
                'Exhaust system security, particulate filter (DPF) warning lamp check',
                'Bodywork inspection: door latches, side-loading doors, and rear barn doors',
                'Cargo restraint rails, heavy-duty ratchet tie-down straps, and bulkhead integrity',
            ],
            'Commercial Heavy Plant & Towing Pre-Trip Inspection' => [
                'Towing hitch, 50mm ball coupling, breakaway cable, and 13-pin electrical socket',
                'Trailer wheel bearings, ramp locking pins, and machine tie-down chains (grade 80)',
                'LOLER cert in vehicle folder and tachograph calibration card valid',
            ],
        ];

        foreach ($vChecks as $catName => $items) {
            $cat = VehicleCheckCategory::firstOrCreate(
                ['slug' => Str::slug($catName)],
                [
                    'name' => $catName,
                    'is_parent' => true,
                    'description' => "Statutory roadworthiness pre-use audit conforming to DVSA and HSE standards.",
                    'status' => true,
                ]
            );

            foreach ($items as $chkText) {
                VehicleCheckChecklist::firstOrCreate(
                    ['vehicle_check_category_id' => $cat->id, 'title' => $chkText],
                    ['has_attributes' => false, 'status' => true]
                );
            }
        }

        // =========================================================================
        // 5. POPULATE 25 H&S CHECKLISTS (Site Safety Audits)
        // =========================================================================
        $hsChecklists = [
            'Working at Height Fall Arrest & Edge Protection Audit',
            'Scaffolding TG20:21 Handover & Weekly Inspection',
            'Hot Works Permit & 60-Minute Fire Watch Audit',
            'Deep Trench Excavation & Ground Support Shoring',
            'Electrical Temporary Supply & RCD Test Register',
            'Safe Use of MEWP & Ground Bearing Pressure Check',
            'Asbestos Pre-Demolition Refurbishment Inspection',
            'Confined Spaces Atmospheric Gas Testing Log',
            'Plant & Pedestrian Segregation Site Traffic Plan',
            'COSHH Chemical Storage & Bund Integrity Audit',
            'Noise Exposure & Hearing Protection Zone Inspection',
            'Vibration White Finger (HAVS) Tool Trigger Time Audit',
            'Manual Handling Heavy Load Risk Assessment',
            'Fire Evacuation Route & Emergency Assembly Point',
            'Site Welfare Facilities & Clean Drinking Water Check',
            'Personal Protective Equipment (PPE) Compliance Audit',
            'Structural Steel Erection Stability & Torque Log',
            'Roof Membrane Hot Air Welding Quality & Flame Check',
            'Concrete Pour Formwork Pressure & Pre-Pour Handover',
            'Lifting Operations Crane Lift Plan & Exclusion Zone',
            'Dust Suppression & Water Spray Silicosis Control',
            'Waste Management SWMP & Skips Segregation Audit',
            'First Aid Box Stock & Trained Responders on Duty',
            'Lone Worker Monitoring & Satellite Check-In Audit',
            'Public Protection & Hoarding Perimeter Security',
        ];

        foreach ($hsChecklists as $hTitle) {
            HsChecklist::firstOrCreate(
                ['slug' => Str::slug($hTitle)],
                [
                    'title' => $hTitle,
                    'short_details' => "Comprehensive statutory site inspection checklist pursuant to CDM Regulations 2015 and HSE standards.",
                    'has_attributes' => false,
                    'type' => 'construction_safety',
                    'status' => true,
                ]
            );
        }

        // =========================================================================
        // 6. POPULATE 30 HS TOOLS (Plant & Tool Inspections)
        // =========================================================================
        $hsTools = [
            'Hilti TE 70-ATC SDS-Max Combihammer (110V)',
            'DeWalt DWS780 305mm Compound Sliding Mitre Saw',
            'Stihl TS 420 350mm Petrol Cut-Off Concrete Saw',
            'Belle Minimix 150 Electric Cement Mixer (110V)',
            'Genie GS-1932 Electric Scissor Lift (7.8m Height)',
            'Makita DHR242Z 18V Brushless SDS Rotary Hammer',
            'Bosch GLL 3-80 360-Degree Multi-Line Laser Level',
            'Paslode IM350+ Cordless Framing Nailer',
            'Leica NA720 Optical Level & Surveying Staff',
            'Clarke 110V Site Transformer 3.3kVA CTE',
            'Husqvarna K770 12-inch Power Cutter (Dust Suppression)',
            'Milwaukee M18 Fuel 1/2-inch High Torque Impact Wrench',
            'C-Scope CXL4 Cable Avoidance Tool & Signal Transmitter',
            'Trend T11EK 1/2-inch Variable Speed Precision Router',
            'Numatic NVH 180 Commercial HEPA Class M Dust Extractor',
            'Stephill SE2700 2.7kVA Honda Petrol Generator',
            'Defender 110V LED Uplight Floor Floodlight',
            'Hilti DD 150-U Diamond Core Wet/Dry Drilling Rig',
            'Spit Pulsa 800P+ Cordless Gas Nail Gun for Drywall',
            'Altrad Belle PCLX 320 Forward Plate Compactor',
            'Senco Fusion F-18 Finish Nailer',
            'Karcher HD 7/12-4 M Plus Professional Pressure Washer',
            'Festool Kapex KS 120 Sliding Compound Mitre Saw',
            'Youngman ProDeck 2.5m Mobile Platform Ladder',
            'Eibenstock EMF 180.2 Wall Chaser (110V Twin Blade)',
            'Honda WT30X 3-inch Trash Water Pump',
            'Bostitch 110V Coil Nailer for Structural Decking',
            'Rothenberger Rocam 4 Plus Inspection Camera',
            'Fluke 1664 FC Multifunction Electrical Installation Tester',
            'Master B 3.3 EPB 3.3kW 110V Portable Fan Heater',
        ];

        foreach ($hsTools as $tName) {
            $tool = HsTool::firstOrCreate(
                ['slug' => Str::slug($tName)],
                [
                    'tool_name' => $tName,
                    'description' => "Site equipment item certified under PUWER and statutory 110V electrical PAT testing protocols.",
                    'status' => 1,
                ]
            );

            // Log checkup for tool
            HsToolCheckup::firstOrCreate(
                ['hs_tool_id' => $tool->id],
                [
                    'tool_name' => $tool->tool_name,
                    'checkup_name' => 'Statutory PUWER / PAT Safety Inspection',
                    'checkup_date' => Carbon::now()->subDays(rand(1, 14))->toDateString(),
                    'performed_by' => $chloe->name,
                    'status' => 'completed',
                    'next_checkup_date' => Carbon::now()->addMonths(6)->toDateString(),
                    'remarks' => 'PAT tested, cable undamaged, mechanical guards intact, verified safe for site deployment.',
                ]
            );
        }

        // =========================================================================
        // 7. POPULATE 100 RAMS JOB CHECKS (FjobHsCheck)
        // =========================================================================
        $ramsTitles = [
            'Pre-Commencement RAMS Handover & Toolbox Briefing',
            'Working at Height Fall Protection & Harness Audit',
            'Structural Steel Lifting & Crane Exclusion Zone Check',
            'Hot Works Permit & Continuous 60-Minute Fire Watch',
            'Trench Shoring & Deep Foundation Excavation Stability',
            'Electrical Temporary Site Distribution & RCD Test Verification',
            'MEWP Ground Bearing Plate & Operator Harness Anchorage Check',
            'Hazardous Chemical COSHH Control & Bunded Storage Inspection',
            'Asbestos Non-Licensable ACM Removal Containment Audit',
            'Site Traffic Segregation & Pedestrian Crossing Verification',
        ];

        for ($i = 1; $i <= 100; $i++) {
            $job = $jobs[($i - 1) % $jobs->count()];
            $title = $ramsTitles[($i - 1) % count($ramsTitles)];
            $status = ($i % 10 === 0) ? 'pending' : (($i % 4 === 0) ? 'in_progress' : 'completed');

            FjobHsCheck::firstOrCreate(
                [
                    'fjob_id' => $job->id,
                    'hs_check_title' => "{$title} (Ref: RAMS-2026-" . str_pad($i, 3, '0', STR_PAD_LEFT) . ")",
                ],
                [
                    'job_title' => $job->job_name,
                    'checked_by_user' => ($i % 2 === 0) ? $chloe->id : $sarah->id,
                    'check_date' => Carbon::now()->subDays($i % 25)->toDateString(),
                    'remarks' => "Site inspection completed in accordance with CDM Regulations 2015. All operatives briefed and signed off.",
                    'status' => $status,
                ]
            );
        }

        // =========================================================================
        // 8. POPULATE 100 DAILY SITE TASKS (JobSchedule & JobScheduleWorker for James)
        // =========================================================================
        $siteTasks = [
            'Level 4 Drywall Partition Framing & Sound Insulation Batt Installation',
            'Roof Truss Structural Tie Strapping & Gable End Bracing',
            'Primary Soil Drainage Stack Pressure Test & Acoustic Insulation Wrap',
            'Commercial Kitchen 3-Phase Power Distribution 2nd Fix Cabling',
            'Architectural Oak Fire Doorset Hanging & Intumescent Strip Fitting',
            'Structural Steel Beam Splice Bolting & Calibrated Torque Verification',
            'External Rainscreen Cavity Barrier & Fire Stopping Compartmentation',
            'Acoustic Suspended Ceiling T-Bar Grid Laser Alignment',
            'Level 2 Screed Pour & Underfloor Heating Manifold Pressure Hold',
            'Full Perimeter Scaffolding Handover & Netting Inspection',
            'Plant Room Chilled Water Pipework Flange Gasket Torquing',
            'Basement Waterproofing Tanking Membrane Overlap Hot-Weld Inspection',
            'Curtain Wall Glazing Gasket Pressure Seal & Weep Hole Clearing',
            'Fire Alarm Addressable Loop Sounder & Smoke Detector Commissioning',
            'Commercial Lift Shaft Steel Divider Beam Installation & Plumb Check',
        ];

        for ($i = 1; $i <= 100; $i++) {
            $job = $jobs[($i - 1) % $jobs->count()];
            $taskName = $siteTasks[($i - 1) % count($siteTasks)];
            $assignedOp = $operatives[($i - 1) % $operatives->count()];
            $status = ($i % 4 === 0) ? 'completed' : (($i % 2 === 0) ? 'inprogress' : 'pending');

            $schedule = JobSchedule::firstOrCreate(
                [
                    'job_id' => $job->id,
                    'job_title' => "Task #{$i}: {$taskName}",
                ],
                [
                    'job_name' => $job->job_name,
                    'start_date' => Carbon::now()->subDays(rand(1, 15))->toDateString(),
                    'end_date' => Carbon::now()->addDays(rand(2, 20))->toDateString(),
                    'instructions' => "Execute according to architect revision drawing D-{$i}. Wear required PPE. Verify line and level prior to handover.",
                    'status' => $status,
                ]
            );

            JobScheduleWorker::firstOrCreate(
                [
                    'job_schedule_id' => $schedule->id,
                    'user_id' => $assignedOp->id,
                ],
                [
                    'assigned_date' => $schedule->start_date,
                    'completion_date' => ($status === 'completed') ? Carbon::now()->subDays(1)->toDateString() : null,
                    'status' => $status,
                    'notes' => "Operative signed into task pack. Materials checked at site compound.",
                ]
            );

            // Also ensure James O'Connor has tasks assigned directly
            if ($i <= 35) {
                JobScheduleWorker::firstOrCreate(
                    [
                        'job_schedule_id' => $schedule->id,
                        'user_id' => $james->id,
                    ],
                    [
                        'assigned_date' => $schedule->start_date,
                        'completion_date' => ($status === 'completed') ? Carbon::now()->subDays(1)->toDateString() : null,
                        'status' => $status,
                        'notes' => "Site Manager oversight & QA signoff.",
                    ]
                );
            }
        }

        // =========================================================================
        // 9. POPULATE 100 SITE STOCK REQUISITIONS (Michael Chang & Sarah Campbell)
        // =========================================================================
        $warehouses = StockWarehouse::all();
        $stockVariants = StockItemVariant::all();

        if ($warehouses->isNotEmpty() && $stockVariants->isNotEmpty()) {
            for ($i = 1; $i <= 100; $i++) {
                $job = $jobs[($i - 1) % $jobs->count()];
                $wh = $warehouses[($i - 1) % $warehouses->count()];
                $reqStatus = ($i % 6 === 0) ? 'rejected' : (($i % 2 === 0) ? 'issued' : 'approved');

                $req = StockItemRequisition::firstOrCreate(
                    ['requisition_number' => 'REQ-2026-' . str_pad($i + 100, 4, '0', STR_PAD_LEFT)],
                    [
                        'fjob_id' => $job->id,
                        'stock_warehouse_id' => $wh->id,
                        'requisition_date' => Carbon::now()->subDays($i % 30),
                        'status' => $reqStatus,
                        'requested_by' => ($i % 2 === 0) ? $sarah->id : $james->id,
                        'approved_by' => $michael->id,
                        'notes' => 'Urgent site delivery to Compound ' . (($i % 5) + 1) . ' for immediate installation.',
                    ]
                );

                $variant1 = $stockVariants[($i * 2) % $stockVariants->count()];
                $variant2 = $stockVariants[($i * 2 + 1) % $stockVariants->count()];

                StockItemRequisitionDetail::firstOrCreate(
                    ['stock_item_requisition_id' => $req->id, 'stock_item_variant_id' => $variant1->id],
                    ['quantity' => rand(5, 50), 'unit' => 'pcs', 'notes' => 'Priority 1 item']
                );

                StockItemRequisitionDetail::firstOrCreate(
                    ['stock_item_requisition_id' => $req->id, 'stock_item_variant_id' => $variant2->id],
                    ['quantity' => rand(10, 100), 'unit' => 'pcs', 'notes' => 'Batch stock']
                );
            }
        }

        // =========================================================================
        // 10. EXPAND SUBCONTRACTOR PAYMENT CERTIFICATES TO 100 (Priya Patel & Marcus)
        // =========================================================================
        $subs = Subcontractor::all();
        if ($subs->isNotEmpty()) {
            for ($i = 16; $i <= 100; $i++) {
                $sub = $subs[($i - 1) % $subs->count()];
                $job = $jobs[($i - 1) % $jobs->count()];
                $gross = rand(15, 65) * 1000;
                $materials = round($gross * 0.20, 2);
                $labor = $gross - $materials;

                $cisRate = ($sub->cis_tax_status === 'higher') ? 30.00 : (($sub->cis_tax_status === 'gross') ? 0.00 : 20.00);
                $cisDeduction = round($labor * ($cisRate / 100), 2);
                $netPayable = $gross - $cisDeduction;
                $certStatus = ($i % 5 === 0) ? 'draft' : (($i % 2 === 0) ? 'paid' : 'certified');

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
                        'payment_status' => $certStatus,
                        'certified_by' => ($certStatus !== 'draft') ? $priya->id : null,
                        'certified_at' => ($certStatus !== 'draft') ? Carbon::now()->subDays(rand(1, 10)) : null,
                        'notes' => "Interim certificate valuation #{$i} for {$sub->trade_specialism}. HMRC CIS verified.",
                    ]
                );
            }
        }

        // =========================================================================
        // 11. EXPAND COMMERCIAL VARIATIONS TO 100 (Marcus Vance Commercial Director)
        // =========================================================================
        for ($i = 26; $i <= 100; $i++) {
            $job = $jobs[($i - 1) % $jobs->count()];
            $cost = rand(4, 45) * 1000;
            $quoted = round($cost * 1.20, 2);
            $vStatus = ($i % 4 === 0) ? 'submitted' : (($i % 8 === 0) ? 'draft' : 'approved');

            JobVariation::firstOrCreate(
                ['variation_number' => "VAR-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                [
                    'business_id' => $business->id,
                    'job_id' => $job->id,
                    'title' => "Commercial Change Order #{$i} - Client Architectural Revision",
                    'description' => "Architectural instruction revision for MEP rerouting, acoustic baffle additions, and premium partition finish specifications.",
                    'cost_amount' => $cost,
                    'markup_percentage' => 20.00,
                    'quoted_amount' => $quoted,
                    'status' => $vStatus,
                    'client_signer_name' => "Project Director ({$job->job_name})",
                    'client_signer_email' => "commercial@client.local",
                    'signature_data' => 'DATA_URL_ESIGN_VERIFIED',
                    'signed_at' => ($vStatus === 'approved') ? Carbon::now()->subDays(rand(1, 20)) : null,
                    'approved_by' => ($vStatus === 'approved') ? $marcus->id : null,
                    'approved_at' => ($vStatus === 'approved') ? Carbon::now()->subDays(rand(1, 20)) : null,
                ]
            );
        }

        // =========================================================================
        // 12. EXPAND VALUATIONS & RETENTIONS TO 100 (Marcus Vance & Priya Patel)
        // =========================================================================
        for ($i = 26; $i <= 100; $i++) {
            $job = $jobs[($i - 1) % $jobs->count()];
            $gross = rand(50, 280) * 1000;
            $retention = round($gross * 0.05, 2); // 5% retention
            $net = $gross - $retention;
            $appStatus = ($i % 2 === 0) ? 'paid' : 'certified';

            $val = JobValuation::firstOrCreate(
                ['application_number' => "APP-2026-" . str_pad($i + 10, 4, '0', STR_PAD_LEFT)],
                [
                    'business_id' => $business->id,
                    'job_id' => $job->id,
                    'valuation_date' => Carbon::now()->subDays(rand(5, 30))->toDateString(),
                    'period_end' => Carbon::now()->subDays(rand(5, 30))->toDateString(),
                    'cumulative_work_value' => round($gross * 0.90, 2),
                    'materials_on_site_value' => round($gross * 0.10, 2),
                    'gross_valuation' => $gross,
                    'previous_certified_amount' => 0.00,
                    'current_gross_amount' => $gross,
                    'retention_rate' => 5.00,
                    'retention_deduction' => $retention,
                    'net_claim_amount' => $net,
                    'certified_amount' => $net,
                    'status' => $appStatus,
                    'certified_at' => Carbon::now()->subDays(rand(2, 10)),
                    'certified_by' => $marcus->id,
                    'paid_at' => ($appStatus === 'paid') ? Carbon::now()->subDays(2)->toDateString() : null,
                    'notes' => "Interim Payment Application #{$i} certified under JCT Design & Build 2016.",
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

        // =========================================================================
        // 13. EXPAND GOODS RECEIVED NOTES & SUPPLIER BILLS TO 100 (Michael Chang)
        // =========================================================================
        $allPos = PurchaseOrder::with(['items', 'supplier', 'job'])->get();
        $currentGrnCount = GoodsReceivedNote::count();

        if ($currentGrnCount < 100 && $allPos->isNotEmpty()) {
            $grnIdx = $currentGrnCount + 1;
            foreach ($allPos as $po) {
                if ($grnIdx > 100) break;
                if ($po->items->isEmpty()) continue;

                $grn = GoodsReceivedNote::firstOrCreate(
                    ['grn_number' => "GRN-2026-" . str_pad($grnIdx + 100, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'purchase_order_id' => $po->id,
                        'delivery_note_number' => "DN-" . strtoupper(Str::random(8)),
                        'received_date' => Carbon::now()->subDays(rand(1, 20))->toDateString(),
                        'received_by' => $michael->id,
                        'notes' => 'Materials offloaded, checked against delivery ticket, intact.',
                    ]
                );

                foreach ($po->items as $pItem) {
                    GoodsReceivedNoteItem::firstOrCreate(
                        ['goods_received_note_id' => $grn->id, 'purchase_order_item_id' => $pItem->id],
                        ['quantity_received' => $pItem->quantity, 'condition' => 'good']
                    );
                }

                $hasVariance = ($grnIdx % 5 === 0);
                $matchStatus = $hasVariance ? 'variance_hold' : 'approved';
                $subtotal = $po->subtotal > 0 ? $po->subtotal : 1500.00;
                $billTotal = $hasVariance ? ($subtotal * 1.15) : $subtotal;

                SupplierBill::firstOrCreate(
                    ['internal_reference' => "INT-BILL-" . str_pad($grnIdx + 100, 4, '0', STR_PAD_LEFT)],
                    [
                        'business_id' => $business->id,
                        'bill_number' => "BILL-" . substr(strtoupper(Str::slug($po->supplier->name ?? 'SUPP')), 0, 4) . "-" . ($grnIdx + 100),
                        'purchase_order_id' => $po->id,
                        'supplier_id' => $po->supplier_id,
                        'job_id' => $po->job_id,
                        'bill_date' => Carbon::now()->subDays(rand(2, 15))->toDateString(),
                        'due_date' => Carbon::now()->addDays(30)->toDateString(),
                        'subtotal' => $subtotal,
                        'tax_amount' => round($subtotal * 0.20, 2),
                        'total_amount' => round($billTotal * 1.20, 2),
                        'match_status' => $matchStatus,
                        'approval_status' => $hasVariance ? 'pending' : 'approved',
                        'approved_by' => $hasVariance ? null : $michael->id,
                        'approved_at' => $hasVariance ? null : Carbon::now()->subDays(2),
                        'variance_notes' => $hasVariance ? "Price variance surge of +15% over PO contractual agreement." : null,
                    ]
                );

                $grnIdx++;
            }
        }
    }
}
