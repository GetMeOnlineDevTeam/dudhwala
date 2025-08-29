<!--start sidebar-->
<aside class="sidebar-wrapper">
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3">
        <div class="logo-icon">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('assets/images/logo3.png') }}" class="mt-4" alt="Logo">
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
            <li class="{{ request()->routeIs('admin.host.*', 'admin.attendee.*') ? 'mm-active' : '' }}">
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="material-icons-outlined">groups</i></div>
                    <div class="menu-title">Users</div>
                </a>
                <ul>
                    <li
                        class="{{ request()->routeIs('admin.hosts', 'admin.edit.host', 'admin.view.host') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.hosts') }}">
                            <i class="material-icons-outlined">arrow_right</i>Hosts
                        </a>
                    </li>
                    <li
                        class="{{ request()->routeIs('admin.attendees', 'admin.edit.attendee', 'admin.view.attendee') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.attendees') }}">
                            <i class="material-icons-outlined">arrow_right</i>Attendee
                        </a>
                    </li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('workshop.*') ? 'mm-active' : '' }}">
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="material-icons-outlined">event</i></div>
                    <div class="menu-title">Workshop</div>
                </a>
                <ul>
                    <li
                        class="{{ request()->routeIs('admin.workshops') && !request()->has('pending_status') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.workshops') }}">
                            <i class="material-icons-outlined">arrow_right</i>All
                        </a>
                    </li>

                    <li
                        class="{{ request()->routeIs('admin.workshops') && request('pending_status') === 'pending' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.workshops', ['pending_status' => 'pending']) }}">
                            <i class="material-icons-outlined">arrow_right</i>Pending
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="material-icons-outlined">receipt_long</i>
                    </div>
                    <div class="menu-title">Bookings</div>
                </a>
                <ul>
                    <li class="{{ request()->routeIs('admin.bookings', 'admin.bookings') ? 'mm-active' : '' }}"><a
                            href="{{route('admin.bookings')}}"><i
                                class="material-icons-outlined">arrow_right</i>Bookings</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.bookings', 'admin.bookings') ? 'mm-active' : '' }}"><a
                            href="{{route('admin.refunds')}}"><i
                                class="material-icons-outlined">arrow_right</i>Refunds</a>
                    </li>
                    {{-- <li><a href="ecommerce-products.html"><i
                                class="material-icons-outlined">arrow_right</i>Transactions</a>
                    </li>
                    <li><a href="ecommerce-customers.html"><i
                                class="material-icons-outlined">arrow_right</i>Disputes</a>
                    </li>
                    <li><a href="ecommerce-customer-details.html"><i
                                class="material-icons-outlined">arrow_right</i>Payouts</a>
                    </li> --}}
                </ul>
            </li>
            <li class="{{ request()->routeIs('admin.banners') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.banners') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">photo_library</i></div>
                    <div class="menu-title">Banner</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.categories') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.categories') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">category</i></div>
                    <div class="menu-title">Categories</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.neighborhoods') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.neighborhoods') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">location_city</i></div>
                    <div class="menu-title">Neighborhood</div>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.topics') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.topics') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">label</i></div>
                    <div class="menu-title">Topics</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.enquiries') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.enquiries') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">mark_email_unread</i></div>
                    <div class="menu-title">Enquiry</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.reviews') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.reviews') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">reviews</i></div>
                    <div class="menu-title">Reviews</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.badges') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.badges') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">military_tech</i></div>
                    <div class="menu-title">Badge</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.testimonials') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.testimonials') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">rate_review</i></div>
                    <div class="menu-title">Testimonials</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.coupons.*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.coupons.index') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">local_offer</i></div>
                    <div class="menu-title">Coupons</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.artisan.partners') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.artisan.partners') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">handshake</i></div>
                    <div class="menu-title">Artisan Partners</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.reels') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.reels') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">movie</i></div>
                    <div class="menu-title">Reels</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.social.icons') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.social.icons') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">movie</i></div>
                    <div class="menu-title">Social Icons</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.socials') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.socials') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">share</i></div>
                    <div class="menu-title">Site Social Icons</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.support.response') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.support.response') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">support</i></div>
                    <div class="menu-title">Support Response</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.commission') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.commission') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">monetization_on</i></div>
                    <div class="menu-title">Commission</div>
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.featured-plans') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.featured-plans') }}">
                    <div class="parent-icon"><i class="material-icons-outlined">star</i></div>
                    <div class="menu-title">Featured Plans</div>
                </a>
            </li>
            {{-- <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="material-icons-outlined">verified_user</i>
                    </div>
                    <div class="menu-title">Verification</div>
                </a>
                <ul>
                    <li><a href="component-alerts.html"><i class="material-icons-outlined">arrow_right</i>Host
                            Verifications</a>
                    </li>
                    <li><a href="component-accordions.html"><i class="material-icons-outlined">arrow_right</i>Violations
                            & Reports</a>
                    </li>
                </ul>
            </li> --}}

            <li class="menu-label">Others</li>
            {{-- <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="material-icons-outlined">support_agent</i>
                    </div>
                    <div class="menu-title">Support</div>
                </a>
                <ul>
                    <li><a href="form-elements.html"><i class="material-icons-outlined">arrow_right</i>Support
                            Tickets</a>
                    </li>
                    <li><a href="form-input-group.html"><i class="material-icons-outlined">arrow_right</i>Feedback</a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="material-icons-outlined">campaign</i>
                    </div>
                    <div class="menu-title">Marketing</div>
                </a>
                <ul>
                    <li><a href="table-basic-table.html"><i class="material-icons-outlined">arrow_right</i>Highlights &
                            Banners</a>
                    </li>
                    <li><a href="table-datatable.html"><i class="material-icons-outlined">arrow_right</i>Featured
                            Workshops</a>
                    </li>
                    <li><a href="table-datatable.html"><i class="material-icons-outlined">arrow_right</i>Campaigns</a>
                    </li>
                </ul>
            </li> --}}
            <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="material-icons-outlined">settings</i>
                    </div>
                    <div class="menu-title">Settings</div>
                </a>
                <ul>
                    <li><a href="{{route('admin.edit.setting')}}"><i
                                class="material-icons-outlined">arrow_right</i>Configuration</a>
                    </li>
                    <li><a href="{{route('admin.pages.edit')}}"><i
                                class="material-icons-outlined">arrow_right</i>Policy
                            Management</a>
                    </li>
                    {{-- <li><a href="app-fullcalender.html"><i class="material-icons-outlined">arrow_right</i>Policy
                            Management</a>
                    </li>
                    <li><a href="app-to-do.html"><i class="material-icons-outlined">arrow_right</i>Platform Settings</a>
                    </li> --}}
                </ul>
            </li>
            <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="material-icons-outlined">help_outline </i>
                    </div>
                    <div class="menu-title">Faqs</div>
                </a>
                <ul>
                    <li><a href="{{route('admin.edit.attendee.faqs')}}"><i
                                class="material-icons-outlined">arrow_right</i>Attendee</a>
                    </li>
                    <li><a href="{{route('admin.edit.host.faqs')}}"><i
                                class="material-icons-outlined">arrow_right</i>Host</a>
                    </li>
                </ul>
            </li>


        </ul>
        <!--end navigation-->
    </div>
    <div class="">

    </div>
</aside>
<!--end sidebar-->