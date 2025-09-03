@php
    $u = auth('admin')->user();

    // Active states for parent menus
    $activeVenuesGroup    = request()->routeIs('admin.venues*') || request()->routeIs('admin.schedule*');
    $activeCommunityGroup = request()->routeIs('admin.community-members*') || request()->routeIs('admin.community-moments*');
    $activeSupportGroup   = request()->routeIs('admin.contact-requests*') || request()->routeIs('admin.money-back*') || request()->routeIs('admin.settlements*');
    $activeSiteGroup      = request()->routeIs('admin.banner.*') || request()->routeIs('admin.configurations.*') || request()->routeIs('admin.policy.*');

    // Visibility for parent menus (true if user has any child permission)
    $canVenuesGroup = (
        $u?->can('venues.view') ||
        $u?->can('venues.create') ||
        $u?->can('venues.edit') ||
        $u?->can('venues.delete') ||
        $u?->can('schedule.view') ||
        $u?->can('schedule.create') ||
        $u?->can('schedule.edit') ||
        $u?->can('schedule.delete')
    );

    $canCommunityGroup = (
        $u?->can('community_members.view') || $u?->can('community_members.create') ||
        $u?->can('community_members.edit') || $u?->can('community_members.delete') ||
        $u?->can('community_members.priority') ||
        $u?->can('community_moments.view')   || $u?->can('community_moments.create') ||
        $u?->can('community_moments.edit')   || $u?->can('community_moments.delete')
    );

    $canSupportGroup = (
        $u?->can('contact_requests.view') || $u?->can('contact_requests.show') || $u?->can('contact_requests.delete') ||
        $u?->can('settlement.view') || $u?->can('settlement.create') || $u?->can('settlement.update_status')
    );

    $canSiteGroup = (
        $u?->can('banner.edit') || $u?->can('banner.update') ||
        $u?->can('configurations.view') || $u?->can('configurations.update') ||
        $u?->can('policy.view') || $u?->can('policy.edit') || $u?->can('policy.update')
    );

    $canInvoices = ($u?->can('invoices.create') || $u?->can('invoices.download'));
    $canOffline  = $u?->can('offline_bookings.create');

    $isSuperadmin = strtolower($u->role ?? '') === 'superadmin';
@endphp

<!--start sidebar-->
<aside class="sidebar-wrapper">

    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
        <div class="logo-icon w-100 text-center">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo"
                     style="width:clamp(28px, 25%, 160px); height:auto; display:block;">
            </a>
        </div>
        <button class="sidebar-close btn btn-link p-0">
            <span class="material-icons-outlined">close</span>
        </button>
    </div>

    <div class="sidebar-nav" data-simplebar="true">
        <!--navigation-->
        <ul class="metismenu" id="sidenav">

            @can('dashboard.view')
            <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">dashboard</i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
            @endcan

            @can('users.view')
            <li class="{{ request()->routeIs('admin.users*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.users') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">people</i></div>
                    <div class="menu-title">Users</div>
                </a>
            </li>
            @endcan

            @can('bookings.view')
            <li class="{{ request()->routeIs('admin.bookings*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.bookings') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">book</i></div>
                    <div class="menu-title">Bookings</div>
                </a>
            </li>
            @endcan

            {{-- Venues & Schedule --}}
            @if ($canVenuesGroup)
            <li class="{{ $activeVenuesGroup ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void(0);">
                    <div class="parent-icon"><i class="material-icons-outlined">location_city</i></div>
                    <div class="menu-title">Venues &amp; Schedule</div>
                </a>
                <ul>
                    @canany(['venues.view','venues.create','venues.edit','venues.delete'])
                    <li class="{{ request()->routeIs('admin.venues*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.venues') }}">
                            <i class="material-icons-outlined">chevron_right</i> Venues
                        </a>
                    </li>
                    @endcanany

                    @canany(['schedule.view','schedule.create','schedule.edit','schedule.delete'])
                    <li class="{{ request()->routeIs('admin.schedule*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.schedule') }}">
                            <i class="material-icons-outlined">chevron_right</i> Schedule
                        </a>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endif

            {{-- Community --}}
            @if ($canCommunityGroup)
            <li class="{{ $activeCommunityGroup ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void(0);">
                    <div class="parent-icon"><i class="material-icons-outlined">groups</i></div>
                    <div class="menu-title">Community</div>
                </a>
                <ul>
                    @canany(['community_members.view','community_members.create','community_members.edit','community_members.delete','community_members.priority'])
                    <li class="{{ request()->routeIs('admin.community-members*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.community-members') }}">
                            <i class="material-icons-outlined">chevron_right</i> Members
                        </a>
                    </li>
                    @endcanany

                    @canany(['community_moments.view','community_moments.create','community_moments.edit','community_moments.delete'])
                    <li class="{{ request()->routeIs('admin.community-moments*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.community-moments') }}">
                            <i class="material-icons-outlined">chevron_right</i> Moments
                        </a>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endif

            {{-- Support & Finance --}}
            @if ($canSupportGroup)
            <li class="{{ $activeSupportGroup ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void(0);">
                    <div class="parent-icon"><i class="material-icons-outlined">support_agent</i></div>
                    <div class="menu-title">Support &amp; Finance</div>
                </a>
                <ul>
                    @canany(['contact_requests.view','contact_requests.show','contact_requests.delete'])
                    <li class="{{ request()->routeIs('admin.contact-requests*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.contact-requests') }}">
                            <i class="material-icons-outlined">chevron_right</i> Contact Requests
                        </a>
                    </li>
                    @endcanany

                    @canany(['settlement.view','settlement.create','settlement.update_status'])
                    <li class="{{ request()->routeIs('admin.settlements*') || request()->routeIs('admin.money-back*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.money-back.index') }}">
                            <i class="material-icons-outlined">chevron_right</i> Settlement
                        </a>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endif

            {{-- Site Settings --}}
            @if ($canSiteGroup)
            <li class="{{ $activeSiteGroup ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void(0);">
                    <div class="parent-icon"><i class="material-icons-outlined">settings</i></div>
                    <div class="menu-title">Site Settings</div>
                </a>
                <ul>
                    @canany(['banner.edit','banner.update'])
                    <li class="{{ request()->routeIs('admin.banner.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.banner.edit') }}">
                            <i class="material-icons-outlined">chevron_right</i> Homepage Banner
                        </a>
                    </li>
                    @endcanany

                    @canany(['configurations.view','configurations.update'])
                    <li class="{{ request()->routeIs('admin.configurations.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.configurations.index') }}">
                            <i class="material-icons-outlined">chevron_right</i> Configurations
                        </a>
                    </li>
                    @endcanany

                    @can('policy.view')
                    <li class="{{ request()->routeIs('admin.policy.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.policy.index') }}">
                            <i class="material-icons-outlined">chevron_right</i> Policy Management
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- Invoices / Payments --}}
            @if ($canInvoices)
            <li class="{{ request()->routeIs('admin.invoices.*') || request()->routeIs('admin.payments.*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.invoices.create') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">receipt_long</i></div>
                    <div class="menu-title">Invoices / Payments</div>
                </a>
            </li>
            @endif

            {{-- Offline Bookings --}}
            @if ($canOffline)
            <li class="{{ request()->routeIs('admin.offline-bookings*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.offline-bookings.create') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">event</i></div>
                    <div class="menu-title">Offline Bookings</div>
                </a>
            </li>
            @endif

            {{-- Superadmin-only --}}
            @if ($isSuperadmin)
            <li class="{{ request()->routeIs('admin.permissions.admin') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.permissions.admin') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">key</i></div>
                    <div class="menu-title">Admin Permissions</div>
                </a>
            </li>
            @endif

        </ul>
        <!--end navigation-->
    </div>

</aside>
<!--end sidebar-->
