<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            {!! strip_tags($appbrand->business_brand_logo_on_top_left ?? 'ERP', '<span><svg><path><img><b><strong>') !!}
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- 1. Universal Dashboard -->
        <li class="menu-item {{ request()->is('home') || request()->is('/') ? 'active' : '' }}">
            <a href="{{ url('/') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-apps"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- 2. System Administration (Super Admin ONLY) -->
        @hasrole('super_admin')
            <li class="menu-header"><span class="menu-header-text">Administration & Security</span></li>
            <li class="menu-item {{ request()->is('users*') || request()->is('roles*') || request()->is('permissions*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-shield-lock"></i>
                    <div data-i18n="Manage Users">User & Access Control</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                        <a href="{{ url('users') }}" class="menu-link">
                            <div data-i18n="Users">User Directory</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('roles*') ? 'active' : '' }}">
                        <a href="{{ url('roles') }}" class="menu-link">
                            <div data-i18n="Roles">Roles & RBAC</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('permissions*') ? 'active' : '' }}">
                        <a href="{{ url('permissions') }}" class="menu-link">
                            <div data-i18n="Permission">Permissions Matrix</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 3. Commercial Contracting & Live WIP (Super Admin, Commercial Director, Lead Quantity Surveyor) -->
        @hasanyrole('super_admin|commercial-director|quantity-surveyor|accounts-manager')
            <li class="menu-header"><span class="menu-header-text">Commercial Contracts & WIP</span></li>
            <li class="menu-item {{ request()->is('commercial*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-chart-candle"></i>
                    <div data-i18n="Commercial Contracting">Commercial Contracts</div>
                </a>
                <ul class="menu-sub">
                    @hasanyrole('super_admin|commercial-director')
                    <li class="menu-item {{ request()->is('commercial/wip*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.wip.portfolio') }}" class="menu-link">
                            <div data-i18n="WIP Job Costing">Live WIP 4-Pillar Costing</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('commercial/variations*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.variations.index') }}" class="menu-link">
                            <div data-i18n="Variations">Client Variations (E-Sign)</div>
                        </a>
                    </li>
                    @endhasrole

                    <li class="menu-item {{ request()->is('commercial/valuations*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.valuations.index') }}" class="menu-link">
                            <div data-i18n="Valuations">Payment Applications</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('commercial/retentions*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.retentions.index') }}" class="menu-link">
                            <div data-i18n="Retentions">Retention Ledger</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 4. Subcontractor CIS (Super Admin, Lead Quantity Surveyor, Commercial Director) -->
        @hasanyrole('super_admin|commercial-director|quantity-surveyor|accounts-manager')
            <li class="menu-item {{ request()->is('subcontractor*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-hard-hat"></i>
                    <div data-i18n="Subcontractors">Subcontractor CIS</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('subcontractors') || (request()->is('subcontractors/*') && !request()->is('subcontractors/certificates*') && !request()->is('subcontractors/cis-monthly-return*')) ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.index') }}" class="menu-link">
                            <div data-i18n="Subcontractors">Subcontractors Directory</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('subcontractors/certificates*') ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.certificates.index') }}" class="menu-link">
                            <div data-i18n="Payment Certificates">Payment Certificates</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('subcontractors/cis-monthly-return*') ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.cis-monthly-return') }}" class="menu-link">
                            <div data-i18n="HMRC CIS300 Return">HMRC CIS300 Return</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 5. Procurement & 3-Way Matching (Super Admin, Commercial Director, Procurement Officer, Lead Quantity Surveyor) -->
        @hasanyrole('super_admin|commercial-director|procurement-officer|inventory-manager|quantity-surveyor|accounts-manager')
            <li class="menu-header"><span class="menu-header-text">Supply Chain & Procurement</span></li>
            <li class="menu-item {{ request()->is('procurement*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-truck-delivery"></i>
                    <div data-i18n="Procurement">Procurement & 3-Way</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('procurement/suppliers*') ? 'active' : '' }}">
                        <a href="{{ route('procurement.suppliers.index') }}" class="menu-link">
                            <div data-i18n="Suppliers">Suppliers Directory</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('procurement/purchase-orders*') ? 'active' : '' }}">
                        <a href="{{ route('procurement.purchase-orders.index') }}" class="menu-link">
                            <div data-i18n="Purchase Orders">Purchase Orders</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('procurement/grns*') ? 'active' : '' }}">
                        <a href="{{ route('procurement.grns.index') }}" class="menu-link">
                            <div data-i18n="Goods Received">Goods Received (GRN)</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('procurement/supplier-bills*') ? 'active' : '' }}">
                        <a href="{{ route('procurement.supplier-bills.index') }}" class="menu-link">
                            <div data-i18n="3-Way Matching">3-Way Match Bills</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 6. Labor & Timesheets (Super Admin, Senior Site Manager, Commercial Director, Quantity Surveyor) -->
        @hasanyrole('super_admin|commercial-director|site-manager|lead-job-manager|quantity-surveyor|accounts-manager')
            <li class="menu-item {{ request()->is('labor*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-clock"></i>
                    <div data-i18n="Labor">Labor & Timesheets</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('labor/timesheets*') ? 'active' : '' }}">
                        <a href="{{ route('labor.timesheets.index') }}" class="menu-link">
                            <div data-i18n="Timesheets">Timesheets Approval</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('labor/rate-cards*') ? 'active' : '' }}">
                        <a href="{{ route('labor.rate-cards.index') }}" class="menu-link">
                            <div data-i18n="Rate Cards">Burdened Rate Cards</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 7. Projects, Scheduling & Operations (Super Admin, Project Engineer, Senior Site Manager, Office Manager) -->
        @hasanyrole('super_admin|project-engineer|job-manager|site-manager|lead-job-manager|office-manager')
            <li class="menu-header"><span class="menu-header-text">Projects & Operations</span></li>
            <li class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Active Jobs</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('scheduler') || request()->is('scheduler/*') ? 'active' : '' }}">
                <a href="{{ url('scheduler') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-calendar-bolt"></i>
                    <div data-i18n="Scheduler">Job Scheduler</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('job-overviews') || request()->is('job-overviews/*') ? 'active' : '' }}">
                <a href="{{ url('job-overviews') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-file-info"></i>
                    <div data-i18n="Job Overviews">Job Overviews</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('tasks*') ? 'active' : '' }}">
                <a href="{{ url('tasks') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-list-check"></i>
                    <div data-i18n="Site Tasks">Daily Site Tasks</div>
                </a>
            </li>
        @endhasrole

        <!-- 8. Field Hub PWA (Super Admin, Project Engineer, Senior Site Manager, Staff) -->
        @hasanyrole('super_admin|project-engineer|job-manager|site-manager|lead-job-manager|staff')
            <li class="menu-item {{ request()->is('field/*') ? 'active' : '' }}">
                <a href="{{ route('field.jobs.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-device-mobile"></i>
                    <div data-i18n="Field Hub">Field Hub (Mobile PWA)</div>
                </a>
            </li>
        @endhasrole

        <!-- 9. Plant & Equipment (Super Admin, Project Engineer, Senior Site Manager, Procurement Officer, Commercial Director, H&S Auditor) -->
        @hasanyrole('super_admin|project-engineer|job-manager|site-manager|lead-job-manager|procurement-officer|inventory-manager|compliance-auditor|health-safety-manager|commercial-director')
            <li class="menu-item {{ request()->is('plant-hires*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-crane"></i>
                    <div data-i18n="Plant Hire">Plant & Equipment</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('plant-hires') || (request()->is('plant-hires/*') && !request()->is('plant-hires/alerts*') && !request()->is('plant-hires/create*')) ? 'active' : '' }}">
                        <a href="{{ route('plant-hires.index') }}" class="menu-link">
                            <div data-i18n="Hire Register">Hire Register</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('plant-hires/create') ? 'active' : '' }}">
                        <a href="{{ route('plant-hires.create') }}" class="menu-link">
                            <div data-i18n="Book Plant">Book Plant Hire</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('plant-hires/alerts*') ? 'active' : '' }}">
                        <a href="{{ route('plant-hires.alerts') }}" class="menu-link">
                            <div data-i18n="Off-Hire Alerts">Off-Hire & Safety Alerts</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 10. Inventory & Warehouses (Super Admin, Procurement Officer, Project Engineer) -->
        @hasanyrole('super_admin|procurement-officer|inventory-manager|project-engineer|job-manager')
            <li class="menu-header"><span class="menu-header-text">Stock & Inventory</span></li>
            <li class="menu-item {{ request()->is('stockwarehouses*') || request()->is('stockcategories*') || request()->is('stockattributes*') || request()->is('stockitems*') || request()->is('stockitemsinventory*') || request()->is('stockitemrequisitions*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-building-warehouse"></i>
                    <div data-i18n="Manage Inventory">Warehouse & Stock</div>
                </a>
                <ul class="menu-sub">
                    @hasanyrole('super_admin|procurement-officer|inventory-manager')
                    <li class="menu-item {{ request()->is('stockitems*') && !request()->is('stockitemsinventory*') && !request()->is('stockitemrequisitions*') ? 'active' : '' }}">
                        <a href="{{ url('stockitems') }}" class="menu-link">
                            <div data-i18n="Stock Items">Stock Items Directory</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('stockitemsinventory*') ? 'active' : '' }}">
                        <a href="{{ url('stockitemsinventory') }}" class="menu-link">
                            <div data-i18n="Stock Items Inventory">Live Warehouse Stock</div>
                        </a>
                    </li>
                    @endhasrole
                    <li class="menu-item {{ request()->is('stockitemrequisitions*') ? 'active' : '' }}">
                        <a href="{{ url('stockitemrequisitions') }}" class="menu-link">
                            <div data-i18n="Stock Item Requisitions">Site Requisitions</div>
                        </a>
                    </li>
                    @hasanyrole('super_admin|procurement-officer|inventory-manager')
                    <li class="menu-item {{ request()->is('stockwarehouses*') ? 'active' : '' }}">
                        <a href="{{ url('stockwarehouses') }}" class="menu-link">
                            <div data-i18n="Stock Warehouses">Warehouses</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('stockcategories*') ? 'active' : '' }}">
                        <a href="{{ url('stockcategories') }}" class="menu-link">
                            <div data-i18n="Stock Categories">Stock Categories</div>
                        </a>
                    </li>
                    @endhasrole
                </ul>
            </li>
        @endhasrole

        <!-- 11. Health, Safety, RAMS & Audits (Super Admin, H&S Compliance Auditor, Project Engineer, Senior Site Manager) -->
        @hasanyrole('super_admin|compliance-auditor|health-safety-manager|project-engineer|job-manager|site-manager|lead-job-manager')
            <li class="menu-header"><span class="menu-header-text">Health & Safety</span></li>
            <li class="menu-item {{ request()->is('fjob-hs-check*') || request()->is('hs-checklists*') || request()->is('hs-tools*') || request()->is('certificates*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-shield-check"></i>
                    <div data-i18n="Health and Safety">RAMS & Safety Audits</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('fjob-hs-check*') ? 'active' : '' }}">
                        <a href="{{ url('fjob-hs-check') }}" class="menu-link">
                            <div data-i18n="RAMS Jobs">RAMS Safety Checks</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('hs-checklists*') ? 'active' : '' }}">
                        <a href="{{ url('hs-checklists') }}" class="menu-link">
                            <div data-i18n="Safety Checklists">Site Safety Audits</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('hs-tools*') ? 'active' : '' }}">
                        <a href="{{ url('hs-tools') }}" class="menu-link">
                            <div data-i18n="Tool Safety">Plant & Tool Inspections</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('certificates*') ? 'active' : '' }}">
                        <a href="{{ url('certificates') }}" class="menu-link">
                            <div data-i18n="Certifications">Compliance & Certs</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 12. Vehicle Fleet & Safety (Super Admin, H&S Compliance Auditor, Fleet Manager) -->
        @hasanyrole('super_admin|compliance-auditor|health-safety-manager|fleet-manager')
            <li class="menu-item {{ request()->is('vehicles*') || request()->is('vehiclecategories*') || request()->is('vehicle-checkings*') || request()->is('drivers*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-car"></i>
                    <div data-i18n="Fleet Safety">Fleet & Compliance</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('vehicles') ? 'active' : '' }}">
                        <a href="{{ url('vehicles') }}" class="menu-link">
                            <div data-i18n="Vehicles">Vehicles Directory</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('vehicle-checkings*') ? 'active' : '' }}">
                        <a href="{{ url('vehicle-checkings') }}" class="menu-link">
                            <div data-i18n="Vehicle Checkings">Vehicle Inspections</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('drivers*') ? 'active' : '' }}">
                        <a href="{{ url('drivers') }}" class="menu-link">
                            <div data-i18n="Drivers">Drivers Directory</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 13. Invoices & Billing (Super Admin, Commercial Director, Lead Quantity Surveyor) -->
        @hasanyrole('super_admin|commercial-director|quantity-surveyor|accounts-manager')
            <li class="menu-header"><span class="menu-header-text">Finance & Billing</span></li>
            <li class="menu-item {{ request()->is('invoices*') ? 'active' : '' }}">
                <a href="{{ route('invoices.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-receipt"></i>
                    <div data-i18n="Invoices">Invoices & CIS Billing</div>
                </a>
            </li>
        @endhasrole

        <!-- 14. Reports & Analytics (Super Admin, Commercial Director, Procurement Officer, Lead QS) -->
        @hasanyrole('super_admin|commercial-director|procurement-officer|inventory-manager|quantity-surveyor|accounts-manager')
            <li class="menu-item {{ request()->is('reports/*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-chart-pie"></i>
                    <div data-i18n="Reports">Reports & Analytics</div>
                </a>
                <ul class="menu-sub">
                    @hasanyrole('super_admin|commercial-director')
                    <li class="menu-item {{ request()->is('reports/conversion-velocity') ? 'active' : '' }}">
                        <a href="{{ route('reports.conversion-velocity') }}" class="menu-link">
                            <div data-i18n="Conversion Velocity">Conversion Velocity</div>
                        </a>
                    </li>
                    @endhasrole
                    <li class="menu-item {{ request()->is('reports/stock-valuation') ? 'active' : '' }}">
                        <a href="{{ route('reports.stock-valuation') }}" class="menu-link">
                            <div data-i18n="Stock Valuation">Stock Valuation Report</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole

        <!-- 15. Customers & CRM (Super Admin, Commercial Director, Office Manager, Lead/Enquiry Managers) -->
        @hasanyrole('super_admin|commercial-director|office-manager|lead-manager|enquiry-manager')
            <li class="menu-header"><span class="menu-header-text">CRM & Clients</span></li>
            <li class="menu-item {{ request()->is('customers*') ? 'active' : '' }}">
                <a href="{{ url('customers') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-cog"></i>
                    <div data-i18n="Customers">Customers & Clients</div>
                </a>
            </li>
        @endhasrole

        <!-- 16. Enquiries & Leads (Super Admin, Office Manager, Enquiry & Lead Managers) -->
        @hasanyrole('super_admin|office-manager|enquiry-manager|lead-manager')
            <li class="menu-item {{ request()->is('enquiries*') || request()->is('enquiry*') ? 'active' : '' }}">
                <a href="{{ url('enquiries') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-message-2-question"></i>
                    <div data-i18n="Enquiries">Enquiries</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('leads*') || request()->is('lead/*') ? 'active' : '' }}">
                <a href="{{ url('leads') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-circle-dotted-letter-l"></i>
                    <div data-i18n="Leads">Leads & Pipeline</div>
                </a>
            </li>
        @endhasrole
    </ul>

    <!-- Sidebar Bottom Controls Dock: Profile, Mode, Configuration -->
    <div class="sidebar-bottom-bar border-top d-flex align-items-center justify-content-between px-3 py-2">
        <!-- Profile Icon / User with Dropup -->
        @auth
        <div class="dropup position-relative">
            <button class="sidebar-footer-btn dropdown-toggle hide-arrow d-flex align-items-center gap-2 p-1 border-0 bg-transparent text-start"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-boundary="viewport"
                    aria-expanded="false"
                    title="User Profile: {{ auth()->user()->name }}">
                <div class="avatar avatar-sm avatar-online flex-shrink-0">
                    <img src="{{ asset('assets/img/front-pages/icons/user.png') }}" alt="{{ auth()->user()->name }}" class="rounded-circle" />
                </div>
                <div class="sidebar-user-text d-flex flex-column text-start overflow-hidden">
                    <span class="sidebar-user-name fw-semibold text-truncate">{{ auth()->user()->name }}</span>
                    <small class="sidebar-user-role text-muted text-truncate">{{ Str::title(str_replace('-', ' ', auth()->user()->getRoleNames()->first() ?? 'Staff')) }}</small>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-start shadow-lg mb-2" style="min-width: 220px; z-index: 1060;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-heading">{{ auth()->user()->name }}</div>
                    <small class="text-muted d-block text-truncate">{{ auth()->user()->email ?? '' }}</small>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('users.myprofile') }}">
                        <i class="bx bx-user me-2 text-primary"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center text-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                        <i class="bx bx-log-out me-2"></i>
                        <span>Logout</span>
                    </a>
                    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
        @endauth

        <!-- Right Controls: Mode Toggle & Gear Configuration (Super Admin ONLY) -->
        <div class="d-flex align-items-center gap-1">
            <!-- Mode Icon -->
            <button type="button"
                    class="sidebar-footer-icon-btn"
                    id="sidebar-theme-toggle"
                    onclick="toggleAppTheme()"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Toggle Light / Dark Mode">
                <i class="bx bx-moon" id="sidebar-theme-icon"></i>
            </button>

            <!-- Gear Icon for System Configuration (Strictly Super Admin ONLY) -->
            @hasrole('super_admin')
            <div class="dropup position-relative">
                <button type="button"
                        class="sidebar-footer-icon-btn dropdown-toggle hide-arrow {{ request()->is('businessinfo*') ? 'active' : '' }}"
                        data-bs-toggle="dropdown"
                        data-bs-boundary="viewport"
                        aria-expanded="false"
                        title="Configuration">
                    <i class="bx bx-cog"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg mb-2" style="min-width: 200px; z-index: 1060;">
                    <li class="dropdown-header text-uppercase small fw-bold">Configuration</li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center {{ request()->is('businessinfo*') ? 'active' : '' }}" href="{{ url('businessinfo') }}">
                            <i class="bx bx-building me-2 text-primary"></i>
                            <span>Business Info</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center {{ request()->is('roles*') ? 'active' : '' }}" href="{{ url('roles') }}">
                            <i class="bx bx-shield-quarter me-2 text-info"></i>
                            <span>Roles & Permissions</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center {{ request()->is('users*') ? 'active' : '' }}" href="{{ url('users') }}">
                            <i class="bx bx-user-check me-2 text-success"></i>
                            <span>Users Management</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endhasrole
        </div>
    </div>
</aside>
