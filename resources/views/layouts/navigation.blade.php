<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo" style="width: 80px;">
                </a>
            </div>

            <!-- Nav Links -->
            <div class="hidden md:flex md:space-x-8 items-center">
                <a href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} text-sm hover:text-green-700">
                    Home
                </a>

                <a href="{{ route('venues') }}"
                    class="{{ request()->routeIs('venues') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} text-sm hover:text-green-700">
                    Venues
                </a>

                <a href="{{ url('about') }}"
                    class="{{ request()->is('about') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} text-sm hover:text-green-700">
                    About Us
                </a>

                <a href="{{ url('contact') }}"
                    class="{{ request()->is('contact') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} text-sm hover:text-green-700">
                    Contact Us
                </a>
            </div>



            <!-- Buttons -->
            <div class="hidden md:flex items-center gap-2">
                @auth
                <a href="{{ route('book.hall') }}"
                    class="bg-green-900 text-white text-sm px-4 py-2 rounded hover:bg-green-950 transition">
                    Book Your Hall
                </a>

                <!-- Show "My Bookings" button if the current route is NOT 'profile.edit' -->
                @if(!Route::is('profile.edit'))
                <a href="{{ route('profile.edit') }}"
                    class="border border-green-900 text-green-900 text-sm px-4 py-2 rounded hover:bg-green-900 hover:text-white transition">
                    My Bookings
                </a>
                @else
                <!-- Show Logout button if the current route is 'profile.edit' -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex text-sm items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        {{ __('Logout') }}
                    </button>
                </form>
                @endif
                @else
                <a href="{{ route('otp.login.form') }}"
                    class="bg-green-900 text-white text-sm px-4 py-2 rounded hover:bg-green-950 transition">
                    Book Your Hall
                </a>
                <a href="{{ route('otp.login.form') }}"
                    class="border border-green-900 text-green-900 text-sm px-4 py-2 rounded hover:bg-green-900 hover:text-white transition">
                    Login
                </a>
                @endauth
            </div>




            <!-- Mobile hamburger -->
            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                        <path :class="{ 'inline-flex': open, 'hidden': !open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" class="md:hidden px-4 pb-4 space-y-2">
        <a href="{{ route('home') }}" class="block text-green-700 font-medium">Home</a>
        <a href="{{ route('venues') }}" class="block text-gray-800 font-medium">Venues</a>
        <a href="{{ route('about') }}" class="block text-gray-800 font-medium">About Us</a>
        <a href="{{ route('contact') }}" class="block text-gray-800 font-medium">Contact Us</a>

        @auth
        <a href="{{ route('book.hall') }}" class="block bg-green-900 text-white px-4 py-2 rounded text-sm w-1/2">Book Your Hall</a>

        <!-- Show "My Bookings" button if the current route is NOT 'profile.edit' -->
        @if(!Route::is('profile.edit'))
        <a href="{{ route('profile.edit') }}" class="block border border-green-900 text-green-900 px-4 py-2 rounded text-sm w-1/2">My Bookings</a>
        @else
        <!-- Show Logout button if the current route is 'profile.edit' -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block bg-red-600 text-white px-4 py-2 rounded text-sm w-1/2">
                {{ __('Logout') }}
            </button>
        </form>
        @endif
        @else
        <a href="{{ route('otp.login.form') }}" class="block bg-green-900 text-white px-4 py-2 rounded text-sm w-1/2">Book Your Hall</a>
        <a href="{{ route('otp.login.form') }}" class="block border border-green-900 text-green-900 px-4 py-2 rounded text-sm w-1/2">Login</a>
        @endauth
    </div>

</nav>