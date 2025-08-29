<!--start header-->
<header class="top-header">
    <nav class="navbar navbar-expand align-items-center gap-4">
        <div class="btn-toggle">
            <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
        </div>

        <ul class="navbar-nav gap-1 nav-right-links align-items-center ms-auto">
            <li class="nav-item d-lg-none mobile-search-btn">
                <a class="nav-link" href="javascript:;"><i class="material-icons-outlined">search</i></a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
                    data-bs-auto-close="outside" data-bs-toggle="dropdown" href="javascript:;"><i
                        class="material-icons-outlined">notifications</i>
                    <span class="badge-notify">5</span>
                </a>
                <div class="dropdown-menu dropdown-notify dropdown-menu-end shadow">
                    <div class="px-3 py-1 d-flex align-items-center justify-content-between border-bottom">
                        <h5 class="notiy-title mb-0">Notifications</h5>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle dropdown-toggle-nocaret option"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-icons-outlined">more_vert</span>
                            </button>
                            <div class="dropdown-menu dropdown-option dropdown-menu-end shadow">
                                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="javascript:;"><i
                                            class="material-icons-outlined fs-6">inventory_2</i>Archive All</a></div>
                                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="javascript:;"><i class="material-icons-outlined fs-6">done_all</i>Mark all
                                        as read</a></div>
                                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="javascript:;"><i class="material-icons-outlined fs-6">mic_off</i>Disable
                                        Notifications</a></div>
                                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="javascript:;"><i class="material-icons-outlined fs-6">grade</i>What's new
                                        ?</a></div>
                                <div>
                                    <hr class="dropdown-divider">
                                </div>
                                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="javascript:;"><i
                                            class="material-icons-outlined fs-6">leaderboard</i>Reports</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="notify-list">
                        <div>
                            <a class="dropdown-item border-bottom py-2" href="javascript:;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="">
                                        <img src="{{ asset('storage/' . authUser()->image) }}" class="rounded-circle"
                                            width="45" height="45" alt="">
                                    </div>
                                    <div class="">
                                        <h5 class="notify-title">Congratulations Jhon</h5>
                                        <p class="mb-0 notify-desc">Many congrats jhon. You have won the gifts.</p>
                                        <p class="mb-0 notify-time">Today</p>
                                    </div>
                                    <div class="notify-close position-absolute end-0 me-3">
                                        <i class="material-icons-outlined fs-6">close</i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Add more notification items as needed -->
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a href="javascript:;" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                    <img src="{{ asset('storage/' . authUser()->image) }}" class="rounded-circle p-1 border"
                        width="45" height="45" alt="Profile Picture">
                </a>
                <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
                    <a class="dropdown-item gap-2 py-2" href="javascript:;">
                        <div class="text-center">
                            <img src="{{ asset('storage/' . authUser()->image) }}"
                                class="rounded-circle p-1 shadow mb-3" width="90" height="90" alt="">
                            <h6 class="mb-0 fw-semibold text-center text-truncate w-100 px-3" style="max-width: 220px;"
                                title="{{ authUser()->name }}">
                                {{ authUser()->name }}
                            </h6>
                        </div>
                    </a>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                            class="material-icons-outlined">person_outline</i>Profile</a>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                            class="material-icons-outlined">local_bar</i>Settings</a>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                            class="material-icons-outlined">dashboard</i>Dashboard</a>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                            class="material-icons-outlined">account_balance</i>Earnings</a>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                            class="material-icons-outlined">cloud_download</i>Downloads</a>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="material-icons-outlined">power_settings_new</i>Logout
                    </a>

                    <form id="logout-form" action="{{ route('host.logout') }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>

                </div>
            </li>
        </ul>
    </nav>
</header>
<!--end top header-->
