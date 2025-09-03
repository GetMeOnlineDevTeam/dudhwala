  <!--start header-->
  <header class="top-header">
      <nav class="navbar navbar-expand align-items-center gap-4">
          <div class="btn-toggle">
              <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
          </div>

          <div class="search-bar flex-grow-1">
              <div class="position-relative">
                  <!-- <input class="form-control rounded-5 px-5 search-control d-lg-block d-none" type="text"
                      placeholder="Search"> -->
                  <!-- <span
                      class="material-icons-outlined position-absolute d-lg-block d-none ms-3 translate-middle-y start-0 top-50">search</span>
                  <span
                      class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 search-close">close</span> -->
                  <div class="search-popup p-3">
                      <div class="card rounded-4 overflow-hidden">
                          <div class="card-header d-lg-none">
                              <div class="position-relative">
                                  <input class="form-control rounded-5 px-5 mobile-search-control" type="text"
                                      placeholder="Search">
                                  <span
                                      class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
                                  <span
                                      class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 mobile-search-close">close</span>
                              </div>
                          </div>
                          <div class="card-body search-content">
                              <p class="search-title">Recent Searches</p>
                              <div class="d-flex align-items-start flex-wrap gap-2 kewords-wrapper">
                                  </a>
                              </div>
                              <hr>
                              <p class="search-title">Tutorials</p>
                              <div class="search-list d-flex flex-column gap-2">
                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="list-icon">
                                          <i class="material-icons-outlined fs-5">play_circle</i>
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title ">Wordpress Tutorials</h5>
                                      </div>
                                  </div>
                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="list-icon">
                                          <i class="material-icons-outlined fs-5">shopping_basket</i>
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title">eCommerce Website Tutorials</h5>
                                      </div>
                                  </div>

                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="list-icon">
                                          <i class="material-icons-outlined fs-5">laptop</i>
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title">Responsive Design</h5>
                                      </div>
                                  </div>
                              </div>

                              <hr>
                              <p class="search-title">Members</p>

                              <div class="search-list d-flex flex-column gap-2">
                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="memmber-img">
                                          <img src="{{ asset('assets/images/avatars/01.png') }}" width="32"
                                              height="32" class="rounded-circle" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title ">Andrew Stark</h5>
                                      </div>
                                  </div>

                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="memmber-img">
                                          <img src="{{ asset('assets/images/avatars/02.png') }}" width="32"
                                              height="32" class="rounded-circle" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title ">Snetro Jhonia</h5>
                                      </div>
                                  </div>

                                  <div class="search-list-item d-flex align-items-center gap-3">
                                      <div class="memmber-img">
                                          <img src="{{ asset('assets/images/avatars/03.png') }}" width="32"
                                              height="32" class="rounded-circle" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="mb-0 search-list-title">Michle Clark</h5>
                                      </div>
                                  </div>

                              </div>
                          </div>
                          <div class="card-footer text-center bg-transparent">
                              <a href="javascript:;" class="btn w-100">See All Search Results</a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <ul class="navbar-nav gap-1 nav-right-links align-items-center">
              <li class="nav-item d-lg-none mobile-search-btn">
                  <a class="nav-link" href="javascript:;"><i class="material-icons-outlined">search</i></a>
              </li>
              <div class="dark-mode">
                  <a href="javascript:;" class="footer-icon dark-mode-icon">
                      <i class="material-icons-outlined">dark_mode</i>
                  </a>
              </div>

              <li class="nav-item dropdown">
                  <!-- <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
                      data-bs-auto-close="outside" data-bs-toggle="dropdown" href="javascript:;"><i
                          class="material-icons-outlined">notifications</i>
                      <span class="badge-notify">5</span>
                  </a> -->
                  <div class="dropdown-menu dropdown-notify dropdown-menu-end shadow">
                      <div class="px-3 py-1 d-flex align-items-center justify-content-between border-bottom">
                          <h5 class="notiy-title mb-0">Notifications</h5>
                          <div class="dropdown">
                              <button class="btn btn-secondary dropdown-toggle dropdown-toggle-nocaret option"
                                  type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="material-icons-outlined">
                                      more_vert
                                  </span>
                              </button>
                              <div class="dropdown-menu dropdown-option dropdown-menu-end shadow">
                                  <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                          href="javascript:;"><i
                                              class="material-icons-outlined fs-6">inventory_2</i>Archive All</a></div>
                                  <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                          href="javascript:;"><i class="material-icons-outlined fs-6">done_all</i>Mark
                                          all as read</a></div>
                                  <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                          href="javascript:;"><i
                                              class="material-icons-outlined fs-6">mic_off</i>Disable Notifications</a>
                                  </div>
                                  <div><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                          href="javascript:;"><i class="material-icons-outlined fs-6">grade</i>What's
                                          new ?</a></div>
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
                                          <img src="{{ asset('assets/images/avatars/01.png') }}"
                                              class="rounded-circle" width="45" height="45" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">Congratulations Jhon</h5>
                                          <p class="mb-0 notify-desc">Many congtars jhon. You have won the gifts.</p>
                                          <p class="mb-0 notify-time">Today</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                          <div>
                              <a class="dropdown-item border-bottom py-2" href="javascript:;">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="user-wrapper bg-primary text-primary bg-opacity-10">
                                          <span>RS</span>
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">New Account Created</h5>
                                          <p class="mb-0 notify-desc">From USA an user has registered.</p>
                                          <p class="mb-0 notify-time">Yesterday</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                          <div>
                              <a class="dropdown-item border-bottom py-2" href="javascript:;">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="">
                                          <img src="{{ asset('assets/images/apps/13.png') }}" class="rounded-circle"
                                              width="45" height="45" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">Payment Recived</h5>
                                          <p class="mb-0 notify-desc">New payment recived successfully</p>
                                          <p class="mb-0 notify-time">1d ago</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                          <div>
                              <a class="dropdown-item border-bottom py-2" href="javascript:;">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="">
                                          <img src="{{ asset('assets/images/apps/14.png') }}" class="rounded-circle"
                                              width="45" height="45" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">New Order Recived</h5>
                                          <p class="mb-0 notify-desc">Recived new order from michle</p>
                                          <p class="mb-0 notify-time">2:15 AM</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                          <div>
                              <a class="dropdown-item border-bottom py-2" href="javascript:;">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="">
                                          <img src="{{ asset('assets/images/avatars/06.png') }}"
                                              class="rounded-circle" width="45" height="45" alt="">
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">Congratulations Jhon</h5>
                                          <p class="mb-0 notify-desc">Many congtars jhon. You have won the gifts.</p>
                                          <p class="mb-0 notify-time">Today</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                          <div>
                              <a class="dropdown-item py-2" href="javascript:;">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="user-wrapper bg-danger text-danger bg-opacity-10">
                                          <span>PK</span>
                                      </div>
                                      <div class="">
                                          <h5 class="notify-title">New Account Created</h5>
                                          <p class="mb-0 notify-desc">From USA an user has registered.</p>
                                          <p class="mb-0 notify-time">Yesterday</p>
                                      </div>
                                      <div class="notify-close position-absolute end-0 me-3">
                                          <i class="material-icons-outlined fs-6">close</i>
                                      </div>
                                  </div>
                              </a>
                          </div>
                      </div>
                  </div>
              </li>

              @php($u = auth('admin')->user())
@if ($u)
<li class="nav-item dropdown">
  <a href="javascript:;" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
    <img
      src="{{ $u->avatar_url ?? asset('storage/logo/logo.png') }}"
      class="rounded-circle p-1 border"
      width="45" height="45" alt="profile">
  </a>

  <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
    <div class="text-center px-3 py-3">
      <img
        src="{{ $u->avatar_url ?? asset('storage/logo/logo.png') }}"
        class="rounded-circle p-1 shadow mb-3"
        width="90" height="90" alt="profile">
      <h6 class="mb-1 fw-bold">
        {{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: ($u->name ?? $u->email) }}
      </h6>
      <span class="badge bg-primary">{{ ucfirst(strtolower($u->role ?? '')) }}</span>
    </div>

    <hr class="dropdown-divider">

    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
       href="#"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="material-icons-outlined">power_settings_new</i>Logout
    </a>
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
      @csrf
    </form>
  </div>
</li>
@endif

          </ul>
      </nav>
  </header>
  <!--end top header-->