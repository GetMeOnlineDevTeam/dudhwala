<!--start sidebar-->
<aside class="sidebar-wrapper">

    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
        <div class="logo-icon w-100 text-center">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo"
                    style="max-width: 72px; max-height: 72px; width: auto; height: auto; display: inline-block;">
            </a>
        </div>
        <button class="sidebar-close btn btn-link p-0">
            <span class="material-icons-outlined">close</span>
        </button>
    </div>

    <div class="sidebar-nav" data-simplebar="true">
        <!--navigation-->
        <ul class="metismenu" id="sidenav">

            <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">dashboard</i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>


            <li class="{{ request()->routeIs('admin.users') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.users') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">people</i></div>
                    <div class="menu-title">Users</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.venues') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.venues') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">location_city</i></div>
                    <div class="menu-title">Venues</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.bookings') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.bookings') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">book</i></div>
                    <div class="menu-title">Bookings</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.schedule') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.schedule') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">event</i></div>
                    <div class="menu-title">Schedule</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.banner.edit') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.banner.edit') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">image</i></div>
                    <div class="menu-title">Homepage Banner</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.community-moments') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.community-moments') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">photo_library</i></div>
                    <div class="menu-title">Community Moments</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.community-members') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.community-members') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">group</i></div>
                    <div class="menu-title">Community Members</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.contact-requests') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.contact-requests') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">mail</i></div>
                    <div class="menu-title">Contact Requests</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.money-back.index') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.money-back.index') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">monetization_on</i></div>
                    <div class="menu-title">Money Back</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.configurations.index') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.configurations.index') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">settings</i></div>
                    <div class="menu-title">Configurations</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.policy.index') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.policy.index') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">policy</i></div>
                    <div class="menu-title">Policy Management</div>
                </a>
            </li>
        </ul>
        <!--end navigation-->
    </div>


</aside>
<!--end sidebar-->
