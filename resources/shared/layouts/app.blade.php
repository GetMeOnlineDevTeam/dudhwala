<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Locatr</title>
    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/images/logo3.png') }}" type="image/png">

    @include('shared.partials.css');
    @yield('css')
</head>

<body>

    @php
        $user = authUser();
        // dd($user);
    @endphp

    @if ($user && $user->hasRole('admin'))
        @include('shared.partials.admin-header')
        @include('shared.partials.admin-sidebar')
    @elseif ($user && $user->hasRole('host'))
        @include('shared.partials.host-header')
        @include('shared.partials.host-sidebar')
    @endif

    <!--start main wrapper-->
    <main class="main-wrapper flex-grow-1">
        @yield('content')
    </main>

    @include('shared.partials.js');

    @yield('js')
    @if (Session::has('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055; min-width: 300px;">
            <div class="alert alert-success border-0 alert-dismissible fade show d-flex align-items-center shadow-sm"
                style="background-color: #02c27a; color: white;" role="alert">

                <div class="font-35 me-3">
                    <span class="material-icons-outlined fs-2" style="color: white;">check_circle</span>
                </div>

                <div class="flex-grow-1">
                    <h6 class="mb-1" style="color: white;">Success</h6>
                    <div>{{ Session::get('success') }}</div>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055; min-width: 300px;">
            <div class="alert alert-danger border-0 alert-dismissible fade show d-flex align-items-center shadow-sm"
                role="alert">
                <div class="font-35 me-3">
                    <span class="material-icons-outlined fs-2">error</span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">Error</h6>
                    <div>{{ Session::get('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

</body>

</html>
