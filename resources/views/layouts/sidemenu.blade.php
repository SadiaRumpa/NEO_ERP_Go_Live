<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                            fill="currentColor" />
                        <path
                            opacity="0.06"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                            fill="#161616" />
                        <path
                            opacity="0.06"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                            fill="#161616" />
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                            fill="currentColor" />
                    </svg>
                </span>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">NEOERP</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{(request()->is('home') || request()->is('/')) ? 'active' : '' }}">
            <a href="{{ url('/') }}" class="menu-link">
                <i class="menu-icon icon-base fa fa-hard-drive"></i>
                <div data-i18n="Dashboards">Dashboards</div>
            </a>
        </li>
        @hasrole('admin')
        <li class="menu-item {{(request()->is('mailtemplates') || request()->is('mailtemplates/*'))  ? 'active' : '' }}">
            <a
                href="{{ url('mailtemplates') }}"
                class="menu-link">
                <i class="menu-icon icon-base fa-regular fa-envelope-open"></i>
                <div data-i18n="Mail Templates">Mail Templates</div>
            </a>
        </li>
        <li class="menu-item {{(request()->is('jobtypes') || request()->is('jobtypes/*'))  ? 'active' : '' }}">
            <a
                href="{{ url('jobtypes') }}"
                class="menu-link">
                <i class="menu-icon icon-base fa-regular fa-square-check"></i>
                <div data-i18n="Job Type">Job Type</div>
            </a>
        </li>
        <!-- Apps & Pages -->
        <li class="menu-item {{(request()->is('users') || request()->is('users/*')) || (request()->is('roles')||request()->is('roles/*')) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div data-i18n="Users">Users</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{(request()->is('users') || request()->is('users/*')) ? 'active' : '' }}">
                    <a href="{{ url('users') }}" class="menu-link">
                        <div data-i18n="List">List</div>
                    </a>
                </li>
                <li class="menu-item {{(request()->is('roles')||request()->is('roles/*')) ? 'active' : '' }}">
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
        @endhasrole
        @hasrole('enquiry-manager')
        <li class="menu-item {{(request()->is('enquiries') || request()->is('enquiries/*') || request()->is('enquiry/*')) ? 'active' : '' }}">
            <a href="{{ url('enquiries') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-access-point"></i>
                <div data-i18n="Enquiries">Enquiries</div>
            </a>
        </li>
        <li class="menu-item {{(request()->is('leads') || request()->is('leads/*') || request()->is('lead/*')) ? 'active' : '' }}">
            <a href="{{ url('leads') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-access-point"></i>
                <div data-i18n="Leads">Leads</div>
            </a>
        </li>
        <li class="menu-item {{(request()->is('customers') || request()->is('customers/*')) ? 'active' : '' }}">
            <a href="{{ url('customers') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div data-i18n="Customers">Customers</div>
            </a>
        </li>
        @endhasrole
        @hasrole('lead-manager')
        <li class="menu-item {{(request()->is('leads') || request()->is('leads/*') || request()->is('lead/*')) ? 'active' : '' }}">
            <a href="{{ url('leads') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-access-point"></i>
                <div data-i18n="Leads">Leads</div>
            </a>
        </li>
        <li class="menu-item {{(request()->is('quotationtemplates') || request()->is('quotationtemplates/*')) ? 'active' : '' }}">
            <a href="{{ url('quotationtemplates') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-app-window"></i>
                <div data-i18n="Quotation Templates">Quotation Templates</div>
            </a>
        </li>
        <li class="menu-item {{(request()->is('customers') || request()->is('customers/*')) ? 'active' : '' }}">
            <a href="{{ url('customers') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div data-i18n="Customers">Customers</div>
            </a>
        </li>
        @endhasrole
        @hasrole('accounts-manager')
        <li class="menu-item {{(request()->is('leads') || request()->is('leads/*') || request()->is('lead/*')) ? 'active' : '' }}">
            <a href="{{ url('leads') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-access-point"></i>
                <div data-i18n="Leads">Leads</div>
            </a>
        </li>
        @endhasrole
    </ul>
</aside>