<footer class="bg-gray-50 text-black py-12">
    <div class="max-w-7xl mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-4 gap-8">

        {{-- 1) Logo & About --}}
        <div class="space-y-4">
            <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo" class="h-16">
            <p class="text-sm text-gray-600 max-w-xs">
                The Dudhwala Muslim community, traditionally associated with the dairy trade, is a vibrant and enterprising group primarily found in parts of India.
            </p>
        </div>

        {{-- 2) Quick Links --}}
        <div>
            <h3 class="font-semibold uppercase mb-4">Quick Links</h3>
            <ul class="space-y-3 text-sm">
                <li>
                    <a href="{{ route('home') }}"
                        class="{{ request()->routeIs('home') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} hover:text-green-700">
                        Home
                    </a>
                </li>
                <!-- <li>
                    <a href="{{ url('gallery') }}"
                        class="{{ request()->is('gallery') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} hover:text-green-700">
                        Gallery
                    </a>
                </li> -->
                <li>
                    <a href="{{ url('about') }}"
                        class="{{ request()->is('about') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} hover:text-green-700">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="{{ route('venues') }}"
                        class="{{ request()->routeIs('venues') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} hover:text-green-700">
                        Venues
                    </a>
                </li>
                <li>
                    <a href="{{ url('contact') }}"
                        class="{{ request()->is('contact') ? 'text-green-700 font-semibold' : 'text-gray-800 font-medium' }} hover:text-green-700">
                        Contact Us
                    </a>
                </li>
            </ul>
        </div>

        {{-- 3) Contact & Policies --}}
        <div>
            @php
            $firstPhone = $footerContacts->firstWhere('contact_type', 'phone');
            $firstEmail = $footerContacts->firstWhere('contact_type', 'email');
            @endphp

            <h3 class="font-semibold uppercase mb-4">Contact Us</h3>
            <ul class="space-y-3 text-sm">
                @if($firstPhone)
                <li class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-800 text-white rounded-md">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <span class="text-gray-900">{{ $firstPhone->contact }}</span>
                </li>
                @endif
                @if($firstEmail)
                <li class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-800 text-white rounded-md">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <span class="text-gray-900">{{ $firstEmail->contact }}</span>
                </li>
                @endif
            </ul>

            <h3 class="font-semibold uppercase mt-8 mb-4">Policies</h3>
            <ul class="space-y-3 text-sm">
                @if (isset($footerPolicies['terms']))
                <li>
                    <a href="{{ route('policies') }}" class="hover:text-green-700">
                        {{ $footerPolicies['terms']->title ?? 'Terms & Conditions' }}
                    </a>
                </li>
                @endif

                @if (isset($footerPolicies['privacy']))
                <li>
                    <a href="{{ route('policies') }}" class="hover:text-green-700">
                        {{ $footerPolicies['privacy']->title ?? 'Privacy Policy' }}
                    </a>
                </li>
                @endif
            </ul>

        </div>

        {{-- 4) Social Media --}}
        <div>
            <h3 class="font-semibold uppercase mb-4">Follow Us</h3>
            <ul class="space-y-3 text-sm">
                <li class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-800 text-white rounded-full">
                        <i class="fab fa-facebook-f"></i>
                    </span>
                    Facebook
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-800 text-white rounded-full">
                        <i class="fab fa-instagram"></i>
                    </span>
                    Instagram
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-800 text-white rounded-full">
                        <i class="fab fa-youtube"></i>
                    </span>
                    YouTube
                </li>
            </ul>
        </div>

    </div>
</footer>