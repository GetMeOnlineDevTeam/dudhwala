<x-app-layout>

    <!-- Banner Image with Scroll‑Into‑View Fade‑In -->
    @if ($banner)
    <section
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        x-cloak
        :class="visible 
      ? 'opacity-100 translate-y-0' 
      : 'opacity-0 translate-y-6'"
        class="h-screen bg-cover bg-center relative transition-all duration-700 ease-out"
        style="background-image: url('{{ asset('storage/' . $banner->banner) }}');">
        <!-- Optional: overlay, heading, etc. -->
    </section>
    @else
    <section
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        x-cloak
        :class="visible 
      ? 'opacity-100 translate-y-0' 
      : 'opacity-0 translate-y-6'"
        class="h-screen bg-gray-100 flex items-center justify-center transition-all duration-700 ease-out">
        <p class="text-gray-500 text-lg">No banner available.</p>
    </section>
    @endif


    <!-- About Section with Scroll‑Into‑View Fade‑In -->
    <section
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        x-cloak
        :class="visible 
        ? 'opacity-100 translate-y-0' 
        : 'opacity-0 translate-y-6'"
        class="bg-white py-16 transition-all duration-700 ease-out">
        <div
            class="max-w-7xl mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            <!-- Left: Text content -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">
                    About Our Community
                </h2>

                <p class="text-gray-700 leading-relaxed mb-6">
                    The Dudhwala Muslim community, traditionally associated with the dairy trade,
                    is a vibrant and enterprising group primarily found in parts of India,
                    especially Maharashtra, Gujarat, and parts of Uttar Pradesh. The name
                    "Dudhwala" literally translates to "milkman" in Hindi and Urdu, reflecting
                    the community’s historical involvement in the production and distribution
                    of milk and dairy products.
                </p>

                <p class="text-gray-700 leading-relaxed mb-6">
                    Beyond its dairy‑centric roots, the Dudhwala community has long embraced education
                    and entrepreneurship. In the late 20th century, many families began diversifying
                    into allied fields—such as dairy processing, transportation, and retail—
                    leveraging their deep understanding of milk production to build small‑scale
                    enterprises and cooperatives. Today, Dudhwala entrepreneurs are found not only in
                    local milk booths and dairies but also in wellness, hospitality, and
                    agri‑technology ventures, blending tradition with innovation to serve both rural
                    and urban markets.
                </p>

                <a
                    href="{{ route('about') }}"
                    class="inline-flex items-center px-4 py-2 border border-green-600 text-green-700 hover:bg-green-600 hover:text-white rounded transition">
                    Know More
                    <svg
                        class="w-4 h-4 ml-2"
                        fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M10.293 15.707a1 1 0 010-1.414L13.586 11H4a1 1 0 110-2h9.586l-3.293-3.293a1 
                           1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <!-- Right: Image Grid -->
            <div class="grid grid-cols-2 gap-4 auto-rows-[1fr]">
                <div class="rounded-md overflow-hidden group relative row-span-1">
                    <img
                        src="{{ asset('storage/about/4.png') }}"
                        alt="Image 1"
                        class="w-full h-full object-cover aspect-[4/3] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105" />
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-2">
                    <img
                        src="{{ asset('storage/about/1.png') }}"
                        alt="Image 2"
                        class="w-full h-full object-cover aspect-[3/4] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105" />
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-2">
                    <img
                        src="{{ asset('storage/about/2.png') }}"
                        alt="Image 3"
                        class="w-full h-full object-cover aspect-[3/4] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105" />
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-1">
                    <img
                        src="{{ asset('storage/about/3.png') }}"
                        alt="Image 4"
                        class="w-full h-full object-cover aspect-[4/3] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105" />
                </div>
            </div>
        </div>
    </section>


    <!-- C -->
    <section
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        x-cloak
        :class="visible ? 'opacity-100' : 'opacity-0'"
        class="bg-white py-16 transition-opacity duration-700 ease-out">
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-10">
                Community Moments
            </h2>

            <!-- Embla Carousel -->
            <div class="embla overflow-hidden" id="communityEmbla">
                <div class="embla__viewport">
                    <div class="embla__container flex gap-6 px-1">
                        @foreach ($communityMoments as $moment)
                        <div class="embla__slide flex-shrink-0 w-[90vw] sm:w-[60vw] md:w-[33.33%] max-w-[400px] aspect-[4/3] overflow-hidden rounded-md shadow-md">
                            <img
                                src="{{ asset('storage/' . $moment->image) }}"
                                alt="{{ $moment->description }}"
                                class="w-full h-full object-cover transform transition-transform duration-300 hover:scale-105" />
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Carousel Caption (optional) -->
            <div class="text-center mt-10 max-w-2xl mx-auto">
                <p class="text-gray-700 text-lg leading-relaxed">
                    “The Muslim community is a diverse and deeply rooted group united by the faith of Islam. Guided by values such as faith, compassion, honesty, and service to others.”
                </p>
            </div>
        </div>
    </section>




    <!-- Venues Carousel -->
    <div id="venuesCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach ($venues as $index => $venue)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <div class="w-full">
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="w-full md:w-1/2 h-[350px] md:h-[500px]">
                            @if ($venue->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $venue->images->first()->image) }}"
                                alt="{{ $venue->name }}"
                                class="w-full h-full object-cover rounded-l-lg d-block" />
                            @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded-l-lg">
                                <span class="text-gray-500">No image available</span>
                            </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="relative w-full md:w-1/2 bg-[#004d32] text-white p-10 flex items-center justify-center">
                            <img src="{{ asset('storage/component/Vector.png') }}" alt="Vector"
                                class="absolute bottom-0 right-0 w-[280px] h-[280px] opacity-10 pointer-events-none" />

                            <div class="relative z-10 w-full max-w-md space-y-6">
                                <h3 class="text-4xl font-bold leading-snug">{{ $venue->name }}</h3>

                                @if ($venue->address)
                                <div class="flex items-start gap-3">
                                    <svg class="w-6 h-6 mt-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z" />
                                    </svg>
                                    <p class="leading-relaxed text-white/90">
                                        {{ $venue->address->addr }}<br>
                                        {{ $venue->address->city }}, {{ $venue->address->state }} - {{ $venue->address->pincode }}
                                    </p>
                                </div>
                                @endif

                                <div class="flex flex-wrap gap-6 text-sm font-medium">
                                    @foreach($activePhone as $contact)
                                    <div class="flex items-center gap-2 text-white/90">
                                        <!-- solid handset icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 
                 1 0 011.11-.21 11.745 11.745 0 003.64.73 1 
                 1 0 011 1V20a1 1 0 01-1 1A16.001 16.001 0 012 
                 4a1 1 0 011-1h3.5a1 1 0 011 1 11.745 
                 11.745 0 00.73 3.64 1 1 0 01-.21 
                 1.11l-2.2 2.2z" />
                                        </svg>
                                        {{ $contact->contact }}
                                    </div>
                                    @endforeach
                                </div>


                                <div class="flex gap-3 pt-2 flex-wrap justify-center sm:justify-start">
                                    <!-- Check Availability Button -->
                                    <a href="{{ route('book.hall') }}" class="bg-white text-black font-semibold px-4 py-2 rounded-md d-flex align-items-center gap-2 hover:bg-light transition">
                                        Check Availability
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M10.854 4.646a.5.5 0 0 0-.708.708L12.293 7.5H1.5a.5.5 0 0 0 0 1h10.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3z" />
                                        </svg>
                                    </a>

                                    <!-- Google Maps Button -->
                                    @if ($venue->address && $venue->address->google_link)
                                    <a href="{{ $venue->address->google_link }}" target="_blank"
                                        class="bg-white text-black font-semibold px-4 py-2 rounded-md d-flex align-items-center gap-2 hover:bg-light transition">
                                        <img src="{{ asset('storage/icons/google-maps.svg') }}" alt="Google Maps" class="w-5 h-5" />
                                        Google Maps
                                    </a>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Keep this inside the carousel -->
        <div class="carousel-indicators">
            @foreach ($venues as $index => $venue)
            <button
                type="button"
                data-bs-target="#venuesCarousel"
                data-bs-slide-to="{{ $index }}"
                class="{{ $index === 0 ? 'active' : '' }}"
                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                aria-label="Slide {{ $index + 1 }}">
            </button>
            @endforeach
        </div>
    </div>


    <!-- Community Members -->
    <section
        x-data="{ showAll: false }"
        class="bg-white py-20 text-center">
        <h2 class="text-2xl md:text-3xl font-bold mb-14 text-gray-900">
            Our Respected Members
        </h2>

        <!-- Top 6 Members -->
        <div
            class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-10 gap-y-16 px-6">
            @foreach ($topMembers as $member)
            <div class="overflow-hidden rounded-md shadow-sm">
                <img
                    src="{{ asset('storage/' . $member->image) }}"
                    alt="{{ $member->name }}"
                    class="w-full h-[390px] object-cover
                           transform transition-transform duration-300
                           hover:scale-105" />
                <h3 class="mt-4 text-[15px] font-semibold text-gray-900">
                    {{ $member->name }}
                </h3>
                <p class="text-sm text-gray-600">
                    {{ $member->designation }}
                </p>
            </div>
            @endforeach
        </div>

        <!-- Remaining Members (collapsed) -->
        <div
            x-cloak
            x-show="showAll"
            x-transition:enter="transition-all ease-out duration-300"
            x-transition:enter-start="opacity-0 max-h-0"
            x-transition:enter-end="opacity-100 max-h-screen"
            x-transition:leave="transition-all ease-in duration-200"
            x-transition:leave-start="opacity-100 max-h-screen"
            x-transition:leave-end="opacity-0 max-h-0"
            class="overflow-hidden max-w-6xl mx-auto mt-12
               grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3
               gap-x-10 gap-y-16 px-6">
            @foreach ($moreMembers as $member)
            <div class="overflow-hidden rounded-md shadow-sm">
                <img
                    src="{{ asset('storage/' . $member->image) }}"
                    alt="{{ $member->name }}"
                    class="w-full h-[390px] object-cover
                           transform transition-transform duration-300
                           hover:scale-105" />
                <h3 class="mt-4 text-[15px] font-semibold text-gray-900">
                    {{ $member->name }}
                </h3>
                <p class="text-sm text-gray-600">
                    {{ $member->designation }}
                </p>
            </div>
            @endforeach
        </div>

        <!-- Toggle Button -->
        <div class="mt-12">
            <button
                @click="showAll = !showAll"
                class="text-green-700 hover:underline text-sm font-semibold inline-flex items-center gap-1">
                <span x-text="showAll ? 'Show Less' : 'Explore More'"></span>
                <svg
                    class="w-4 h-4 transform"
                    :class="showAll ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </section>




    <!-- Venue Gallery with Scroll‑Into‑View Fade‑In & Hover‑Scale -->
    <section
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        x-cloak
        :class="visible ? 'opacity-100' : 'opacity-0'"
        class="bg-white py-16 transition-opacity duration-700 ease-out">
        <div class="max-w-7xl mx-auto px-4">
            {{-- Section Title --}}
            <h2 class="text-center text-xl md:text-2xl font-semibold mb-10">
                Venue Gallery
            </h2>

            {{-- Image Grid --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Row 1: Three small images --}}
                @foreach ($otherImages->slice(0, 3) as $image)
                <div class="overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $image->image) }}"
                        alt="Venue image"
                        class="w-full h-44 object-cover
                               transform transition-transform duration-300
                               hover:scale-105" />
                </div>
                @endforeach

                {{-- Row 2: Cover image spanning all columns --}}
                @if ($coverImage)
                <div class="col-span-3 relative overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $coverImage->image) }}"
                        alt="Cover image"
                        class="w-full h-auto object-cover
                               transform transition-transform duration-300
                               hover:scale-105" />
                    <div style="font-size: 2em;"
                        class="absolute bottom-0 right-0
                               bg-green-800 text-white text-2xl font-semibold
                               px-4 py-2 rounded-tl-md">
                        {{ $coverImage->venue->name ?? 'Gaushiya Hall' }}
                    </div>
                </div>
                @endif

                {{-- Row 3: Three small images --}}
                @foreach ($otherImages->slice(3, 3) as $image)
                <div class="overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $image->image) }}"
                        alt="Venue image"
                        class="w-full h-44 object-cover
                               transform transition-transform duration-300
                               hover:scale-105" />
                </div>
                @endforeach
            </div>
        </div>
    </section>


</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Embla for Community Moments Carousel
        const initEmblaSimple = (emblaId) => {
            const emblaNode = document.querySelector(emblaId);
            if (!emblaNode || !window.EmblaCarousel) return;

            // Initialize Embla carousel with autoplay and smooth transitions
            const embla = EmblaCarousel(emblaNode, {
                loop: true, // Infinite loop
                align: "center", // Center the active slide
                dragFree: true, // Enable drag-to-scroll
                containScroll: "trimSnaps", // Trim the scroll to fit the snaps
                autoplay: true, // Autoplay enabled
                interval: 5000 // Set autoplay interval to 5 seconds
            });
        };

        // Initialize the carousel for Community Moments
        initEmblaSimple("#communityEmbla"); // ✅ Active for Community Moments Carousel
    });
</script>



@endpush