@section('css')
<style>
    <!--plugins
    -->
    /*
<link href="{{ asset('assets/plugins/metismenu/metisMenu.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/metismenu/mm-vertical.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet">
*/
/* (Optional) If you ever need a pure-CSS fallback */
.sidebar-nav {
display: flex;
flex-direction: column;
height: 100%;
}
.sidebar-nav .bottom-menu {
margin-top: auto;
}

</style>
@endsection

<aside class="sidebar-wrapper d-flex flex-column vh-100">
    {{-- Sidebar Header --}}
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3">
        {{-- <div class="logo-icon">
            <img src="{{ asset('assets/images/logo3.png') }}" class="mt-4" width="115" alt="">
    </div> --}}
    <a href="{{ route('admin.dashboard') }}">
        <img src="{{ asset('assets/images/logo3.png') }}" class="mt-4" width="115" alt="Logo">
    </a>
    <button class="sidebar-close btn btn-link p-0">
        <span class="material-icons-outlined">close</span>
    </button>
    </div>

    {{-- Sidebar Navigation --}}
    <div class="sidebar-nav d-flex flex-column flex-grow-1" data-simplebar="true">
        {{-- Top Menu --}}
        <ul class="metismenu px-2" id="sidenav">
            <li class="{{ request()->routeIs('host.dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('host.dashboard') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">home</i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('host.bookings') ? 'mm-active' : '' }}">
                <a href="{{ route('host.bookings') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">event</i></div>
                    <div class="menu-title">Booking</div>
                </a>
            </li>
            <li
                class="{{ request()->routeIs('host.workshops', 'host.add.workshop', 'edit.host.workshop') ? 'mm-active' : '' }}">
                <a href="{{ route('host.workshops') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">layers</i></div>
                    <div class="menu-title">Workshops</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('host.revenue') ? 'mm-active' : '' }}">
                <a href="{{ route('host.revenue') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">bar_chart</i></div>
                    <div class="menu-title">Revenue</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('host.coupons.*') ? 'mm-active' : '' }}">
                <a href="{{ route('host.coupons.index') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">emoji_people</i></div>
                    <div class="menu-title">Coupons</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('host.featured-plans.*') ? 'mm-active' : '' }}">
                <a href="{{ route('host.featured-plans') }}">
                    <div class="parent-icon">
                        <i class="material-icons-outlined">sell</i>
                    </div>
                    <div class="menu-title">Featured Plans</div>
                </a>
            </li>
        </ul>

        {{-- Bottom Menu (automatically pushed down) --}}
        <ul class="metismenu px-2 mt-auto bottom-menu">
            <li>
                <a href="{{ route('host.support') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">help_outline </i></div>
                    <div class="menu-title">FAQS</div>
                </a>
            </li>
            <li>
                <a href="{{ route('host.settings') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">settings</i></div>
                    <div class="menu-title">Settings</div>
                </a>
            </li>
            <li>
                <a href="{{ route('host.support.request') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">support</i></div>
                    <div class="menu-title">Support</div>
                </a>
            </li>
        </ul>
    </div>
</aside>
@section('js')
{{--
    <script src="{{ asset('assets/plugins/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>

<script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script> --}}
@endsection