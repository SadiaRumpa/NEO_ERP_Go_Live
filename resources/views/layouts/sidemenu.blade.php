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
        <!-- Dashboards -->
        <li class="menu-item {{ request()->is('home') || request()->is('/') ? 'active' : '' }}">
            <a href="{{ url('/') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-apps"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>
        @hasanyrole('super_admin|admin')
            <!-- Apps & Pages -->
            <li
                class="menu-item {{ request()->is('users') || request()->is('users/*') || (request()->is('roles') || request()->is('roles/*')) ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-users"></i>
                    <div data-i18n="Manage Users">Manage Users</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('users') || request()->is('users/*') ? 'active' : '' }}">
                        <a href="{{ url('users') }}" class="menu-link">
                            <div data-i18n="Users">Users</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('roles') || request()->is('roles/*') ? 'active' : '' }}">
                        <a href="{{ url('roles') }}" class="menu-link">
                            <div data-i18n="Roles">Roles</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ url('permissions') }}" class="menu-link">
                            <div data-i18n="Permission">Permission</div>
                        </a>
                    </li>

                </ul>
            </li>
            <li class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                <a href="{{ url('customers') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-cog"></i>
                    <div data-i18n="Customers">Customers</div>
                </a>
            </li>
        @endhasrole
        @hasrole('office-manager')
            <li
                class="menu-item {{ request()->is('enquiries') || request()->is('enquiries/*') || request()->is('enquiry/*') ? 'active' : '' }}">
                <a href="{{ url('enquiries') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-message-2-question"></i>
                    <div data-i18n="Enquiries">Enquiries</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('leads') || request()->is('leads/*') || request()->is('lead/*') ? 'active' : '' }}">
                <a href="{{ url('leads') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-circle-dotted-letter-l"></i>
                    <div data-i18n="Leads">Leads</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Jobs</div>
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

            {{-- Settings --}}
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                        <a href="{{ url('customers') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user-cog"></i>
                            <div data-i18n="Customers">Customers</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('jobtypes') || request()->is('jobtypes/*') ? 'active' : '' }}">
                        <a href="{{ url('jobtypes') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-arrows-join"></i>
                            <div data-i18n="Job Type">Job Type</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('mailtemplates') || request()->is('mailtemplates/*') ? 'active' : '' }}">
                        <a href="{{ url('mailtemplates') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-mail-spark"></i>
                            <div data-i18n="Mail Templates">Mail Templates</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('quotationtemplates') || request()->is('quotationtemplates/*') ? 'active' : '' }}">
                        <a href="{{ url('quotationtemplates') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-blockquote"></i>
                            <div data-i18n="Quotation Templates">Quotation Templates</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('users') || request()->is('users/*') ? 'active' : '' }}">
                        <a href="{{ url('users') }}" class="menu-link">
                            <div data-i18n="Manage Users">Manage Users</div>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Manage Inventory --}}
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Manage Inventory">Manage Inventory</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('stockwarehouses') || request()->is('stockwarehouses/*') ? 'active' : '' }}">
                        <a href="{{ url('stockwarehouses') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-building-warehouse"></i>
                            <div data-i18n="Stock Warehouses">Stock Warehouses</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockcategories') || request()->is('stockcategories/*') ? 'active' : '' }}">
                        <a href="{{ url('stockcategories') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-box"></i>
                            <div data-i18n="Stock Categories">Stock Categories</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockattributes') || request()->is('stockattributes/*') ? 'active' : '' }}">
                        <a href="{{ url('stockattributes') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-attribute"></i>
                            <div data-i18n="Stock Attributes">Stock Attributes</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockitems') || request()->is('stockitems/*') ? 'active' : '' }}">
                        <a href="{{ url('stockitems') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-box-archive"></i>
                            <div data-i18n="Stock Items">Stock Items</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockitemsinventory') || request()->is('stockitemsinventory/*') ? 'active' : '' }}">
                        <a href="{{ url('stockitemsinventory') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-archive"></i>
                            <div data-i18n="Stock Items Inventory">Manage Inventory</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockitemrequisitions') || request()->is('stockitemrequisitions/*') ? 'active' : '' }}">
                        <a href="{{ url('stockitemrequisitions') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-clipboard-list"></i>
                            <div data-i18n="Stock Item Requisitions">Requisitions</div>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Manage Vehicle --}}
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Layouts">Manage Vehicle</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('vehiclecategories') || request()->is('vehiclecategories/*') ? 'active' : '' }}">
                        <a href="{{ url('vehiclecategories') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-car"></i>
                            <div data-i18n="Vehicle Categories">Vehicle Categories</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('vehicles') || request()->is('vehicles/*') ? 'active' : '' }}">
                        <a href="{{ url('vehicles') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-car"></i>
                            <div data-i18n="Vehicles">Vehicles</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('drivers') || request()->is('drivers/*') ? 'active' : '' }}">
                        <a href="{{ url('drivers') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user"></i>
                            <div data-i18n="Drivers">Drivers</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('vehicle-checkings') || request()->is('vehicle-checkings/*') ? 'active' : '' }}">
                        <a href="{{ url('vehicle-checkings') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-checks"></i>
                            <div data-i18n="Vehicle Checkings">Vehicle Checkings</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('enquiry-manager')
            <li
                class="menu-item {{ request()->is('enquiries') || request()->is('enquiries/*') || request()->is('enquiry/*') ? 'active' : '' }}">
                <a href="{{ url('enquiries') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-message-2-question"></i>
                    <div data-i18n="Enquiries">Enquiries</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('leads') || request()->is('leads/*') || request()->is('lead/*') ? 'active' : '' }}">
                <a href="{{ url('leads') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-circle-dotted-letter-l"></i>
                    <div data-i18n="Leads">Leads</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                <a href="{{ url('customers') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-cog"></i>
                    <div data-i18n="Customers">Customers</div>
                </a>
            </li>
        @endhasrole
        @hasrole('lead-manager')
            <li
                class="menu-item {{ request()->is('leads') || request()->is('leads/*') || request()->is('lead/*') ? 'active' : '' }}">
                <a href="{{ url('leads') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-circle-dotted-letter-l"></i>
                    <div data-i18n="Leads">Leads</div>
                </a>
            </li>

            <li
                class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Jobs</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                        <a href="{{ url('customers') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user-cog"></i>
                            <div data-i18n="Customers">Customers</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('quotationtemplates') || request()->is('quotationtemplates/*') ? 'active' : '' }}">
                        <a href="{{ url('quotationtemplates') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-blockquote"></i>
                            <div data-i18n="Quotation Templates">Quotation Templates</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('accounts-manager')
            <li
                class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Jobs</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                <a href="{{ url('customers') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-cog"></i>
                    <div data-i18n="Customers">Customers</div>
                </a>
            </li>
        @endhasrole
        @hasrole('job-manager')
            <li
                class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Jobs</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('scheduler') || request()->is('scheduler/*') ? 'active' : '' }}">
                <a href="{{ url('scheduler') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-calendar-bolt"></i>
                    <div data-i18n="Scheduler">Scheduler</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('job-overviews') || request()->is('job-overviews/*') ? 'active' : '' }}">
                <a href="{{ url('job-overviews') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-file-info"></i>
                    <div data-i18n="Job Overviews">Job Overviews</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                        <a href="{{ url('customers') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user-cog"></i>
                            <div data-i18n="Customers">Customers</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('job-attributes') || request()->is('job-attributes/*') ? 'active' : '' }}">
                        <a href="{{ url('job-attributes') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-settings-share"></i>
                            <div data-i18n="Job Attributes">Job Attributes</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('lead-job-manager')
            <li
                class="menu-item {{ request()->is('leads') || request()->is('leads/*') || request()->is('lead/*') ? 'active' : '' }}">
                <a href="{{ url('leads') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-circle-dotted-letter-l"></i>
                    <div data-i18n="Leads">Leads</div>
                </a>
            </li>

            <li
                class="menu-item {{ request()->is('jobs') || request()->is('jobs/*') || request()->is('job/*') ? 'active' : '' }}">
                <a href="{{ url('jobs') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div data-i18n="Jobs">Jobs</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('customers') || request()->is('customers/*') ? 'active' : '' }}">
                        <a href="{{ url('customers') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user-cog"></i>
                            <div data-i18n="Customers">Customers</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('quotationtemplates') || request()->is('quotationtemplates/*') ? 'active' : '' }}">
                        <a href="{{ url('quotationtemplates') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-blockquote"></i>
                            <div data-i18n="Quotation Templates">Quotation Templates</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('inventory-manager')
            <li
                class="menu-item {{ request()->is('stockitemsinventory') || request()->is('stockitemsinventory/*') ? 'active' : '' }}">
                <a href="{{ url('stockitemsinventory') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-archive"></i>
                    <div data-i18n="Stock Items Inventory">Manage Inventory</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('stockitemrequisitions') || request()->is('stockitemrequisitions/*') ? 'active' : '' }}">
                <a href="{{ url('stockitemrequisitions') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-clipboard-list"></i>
                    <div data-i18n="Stock Item Requisitions">Item Requisitions</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('stockwarehouses') || request()->is('stockwarehouses/*') ? 'active' : '' }}">
                        <a href="{{ url('stockwarehouses') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-building-warehouse"></i>
                            <div data-i18n="Stock Warehouses">Stock Warehouses</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockcategories') || request()->is('stockcategories/*') ? 'active' : '' }}">
                        <a href="{{ url('stockcategories') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-box"></i>
                            <div data-i18n="Stock Categories">Stock Categories</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockitems') || request()->is('stockitems/*') ? 'active' : '' }}">
                        <a href="{{ url('stockitems') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-box-archive"></i>
                            <div data-i18n="Stock Items">Stock Items</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('stockattributes') || request()->is('stockattributes/*') ? 'active' : '' }}">
                        <a href="{{ url('stockattributes') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-attribute"></i>
                            <div data-i18n="Stock Attributes">Stock Attributes</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('fleet-manager')
            <li class="menu-item {{ request()->is('vehicles') || request()->is('vehicles/*') ? 'active' : '' }}">
                <a href="{{ url('vehicles') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-car"></i>
                    <div data-i18n="Manage Vehicles">Manage Vehicles</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('drivers') || request()->is('drivers/*') ? 'active' : '' }}">
                <a href="{{ url('drivers') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user"></i>
                    <div data-i18n="Manage Drivers">Manage Drivers</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('vehicle-checkings') || request()->is('vehicle-checkings/*') ? 'active' : '' }}">
                <a href="{{ url('vehicle-checkings') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-checks"></i>
                    <div data-i18n="Vehicle Checkings">Vehicle Checkings</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('vehiclecategories') || request()->is('vehiclecategories/*') ? 'active' : '' }}">
                        <a href="{{ url('vehiclecategories') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-car"></i>
                            <div data-i18n="Vehicle Categories">Vehicle Categories</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('health-safety-manager')
            @php
                $currentJobtype = request()->query('jobtype');
                $jobtypes = DB::table('job_types')->get();
            @endphp
            <li
                class="menu-item {{ request()->is('fjob-hs-check') || request()->is('fjob-hs-check/*') ? 'active open' : '' }}">
                <a href="{{ url('fjob-hs-check') }}" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-checkup-list"></i>
                    <div data-i18n="Job H&S Check">RAMS (Jobs)</div>
                </a>
                <ul class="menu-sub">
                    @foreach ($jobtypes as $jobtype)
                        <li class="menu-item {{ $currentJobtype === $jobtype->slug ? 'active' : '' }}">
                            <a href="{{ url('fjob-hs-check?jobtype=' . $jobtype->slug) }}" class="menu-link">
                                <div data-i18n="{{ $jobtype->name }}">{{ $jobtype->name }}</div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <li class="menu-item {{ request()->is('hs-tools') || request()->is('hs-tools/*') ? 'active' : '' }}">
                <a href="{{ url('hs-tools') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-tool"></i>
                    <div data-i18n="H & S Tools">H & S Tools</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('certificates') || request()->is('certificates/*') ? 'active open' : '' }}">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-brand-databricks"></i>
                    <div data-i18n="staff Training">staff Trainings</div>
                </a>
                <ul class="menu-sub">

                    <li class="menu-item">
                        <a href="#" class="menu-link">
                            <div data-i18n="Training Matrix">Training Matrix</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ request()->is('certificates') || request()->is('certificates/*') ? 'active' : '' }}">
                        <a href="{{ url('certificates') }}" class="menu-link">
                            <div data-i18n="Certificates">Certificates</div>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-layout-sidebar"></i>
                    <div data-i18n="Settings">Settings</div>
                </a>
                <ul class="menu-sub">
                    <li
                        class="menu-item {{ request()->is('hs-checklists') || request()->is('hs-checklists/*') ? 'active' : '' }}">
                        <a href="{{ url('hs-checklists') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-checkup-list"></i>
                            <div data-i18n="Health & Safety Checklists">H&S Checklists</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasrole('staff')
            <li class="menu-item {{ request()->is('tasks') || request()->is('tasks/*') ? 'active' : '' }}">
                <a href="{{ url('tasks') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-tool"></i>
                    <div data-i18n="tasks">Tasks</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('fjob-hs-check') || request()->is('fjob-hs-check/*') ? 'active open' : '' }}">
                <a href="{{ url('fjob-hs-check') }}" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-checkup-list"></i>
                    <div data-i18n="Job H&S Check">RAMS (Jobs)</div>
                </a>
                <ul class="menu-sub">
                    @php
                        $currentJobtype = request()->query('jobtype');
                        $jobtypes = DB::table('job_types')->get();
                    @endphp
                    @foreach ($jobtypes as $jobtype)
                        <li class="menu-item {{ $currentJobtype === $jobtype->slug ? 'active' : '' }}">
                            <a href="{{ url('fjob-hs-check?jobtype=' . $jobtype->slug) }}" class="menu-link">
                                <div data-i18n="{{ $jobtype->name }}">{{ $jobtype->name }}</div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <li class="menu-item {{ request()->is('hs-tools') || request()->is('hs-tools/*') ? 'active' : '' }}">
                <a href="{{ url('hs-tools') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-tool"></i>
                    <div data-i18n="H & S Tools">H & S Tools</div>
                </a>
            </li>
            <li
                class="menu-item {{ request()->is('staff_trainings') || request()->is('staff_trainings/*') ? 'active open' : '' }}">
                <a href="{{ url('staff_trainings') }}" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-brand-databricks"></i>
                    <div data-i18n="staff Training">staff Trainings</div>
                </a>
                <ul class="menu-sub">

                    <li class="menu-item">
                        <a href="#" class="menu-link">
                            <div data-i18n="Training Matrix">Training Matrix</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link">
                            <div data-i18n="Certificates">Certificates</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
        @hasanyrole('super_admin|admin')
            <!-- Apps & Pages -->
            <li
                class="menu-item {{ request()->is('businessinfo') || request()->is('businessinfo/*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-settings"></i>
                    <div data-i18n="Manage Users">Configuration</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('businessinfo') || request()->is('businessinfo/*') ? 'active' : '' }}">
                        <a href="{{ url('businessinfo') }}" class="menu-link">
                            <div data-i18n="Users">Business Info</div>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item {{ request()->is('reports/*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-chart-pie"></i>
                    <div data-i18n="Reports">Reports & Analytics</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('reports/conversion-velocity') ? 'active' : '' }}">
                        <a href="{{ route('reports.conversion-velocity') }}" class="menu-link">
                            <div data-i18n="Conversion Velocity">Conversion Velocity</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('reports/stock-valuation') ? 'active' : '' }}">
                        <a href="{{ route('reports.stock-valuation') }}" class="menu-link">
                            <div data-i18n="Stock Valuation">Stock Valuation</div>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item {{ request()->is('notifications') ? 'active' : '' }}">
                <a href="{{ route('notifications.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-bell"></i>
                    <div data-i18n="Notifications">Notifications</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('invoices') || request()->is('invoices/*') ? 'active' : '' }}">
                <a href="{{ route('invoices.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-receipt"></i>
                    <div data-i18n="Invoices">Invoices & CIS</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('field/*') ? 'active' : '' }}">
                <a href="{{ route('field.jobs.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-device-mobile"></i>
                    <div data-i18n="Field Hub">Field Hub (PWA)</div>
                </a>
            </li>
            <li class="menu-header"><span class="menu-header-text">Commercial & WIP Costing</span></li>
            <li class="menu-item {{ request()->is('procurement*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-truck-delivery"></i>
                    <div data-i18n="Procurement">Procurement & 3-Way</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('procurement/suppliers*') ? 'active' : '' }}">
                        <a href="{{ route('procurement.suppliers.index') }}" class="menu-link">
                            <div data-i18n="Suppliers">Suppliers</div>
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
            <li class="menu-item {{ request()->is('labor*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-clock"></i>
                    <div data-i18n="Labor">Labor & Timesheets</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('labor/timesheets*') ? 'active' : '' }}">
                        <a href="{{ route('labor.timesheets.index') }}" class="menu-link">
                            <div data-i18n="Timesheets">Timesheets</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('labor/rate-cards*') ? 'active' : '' }}">
                        <a href="{{ route('labor.rate-cards.index') }}" class="menu-link">
                            <div data-i18n="Rate Cards">Rate Cards</div>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item {{ request()->is('subcontractor*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-hard-hat"></i>
                    <div data-i18n="Subcontractors">Subcontractor CIS</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('subcontractors') || (request()->is('subcontractors/*') && !request()->is('subcontractors/certificates*') && !request()->is('subcontractors/cis-monthly-return*')) ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.index') }}" class="menu-link">
                            <div data-i18n="Subcontractors">Subcontractors</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('subcontractors/certificates*') ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.certificates.index') }}" class="menu-link">
                            <div data-i18n="Payment Certificates">Payment Certs</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('subcontractors/cis-monthly-return*') ? 'active' : '' }}">
                        <a href="{{ route('subcontractors.cis-monthly-return') }}" class="menu-link">
                            <div data-i18n="HMRC CIS300 Return">HMRC CIS300</div>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item {{ request()->is('plant-hires*') ? 'open active' : '' }}">
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
                            <div data-i18n="Off-Hire Alerts">Off-Hire Alerts</div>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item {{ request()->is('commercial*') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ti tabler-certificate"></i>
                    <div data-i18n="Commercial Contracting">Commercial Contracts</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('commercial/variations*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.variations.index') }}" class="menu-link">
                            <div data-i18n="Variations">Variations (Change Orders)</div>
                        </a>
                    </li>
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
                    <li class="menu-item {{ request()->is('commercial/wip*') ? 'active' : '' }}">
                        <a href="{{ route('commercial.wip.portfolio') }}" class="menu-link">
                            <div data-i18n="WIP Job Costing">Live WIP Job Costing</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasrole
</aside>
