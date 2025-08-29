<x-app-layout>

    <!-- About Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

            <!-- Left: Text content -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">About Our Community</h2>
                <p class="text-gray-700 leading-relaxed mb-6">
                    The Dudhwala Muslim community, traditionally associated with the dairy trade, is a vibrant and enterprising group primarily found in parts of India, especially Maharashtra, Gujarat, and parts of Uttar Pradesh. The name "Dudhwala" literally translates to "milkman" in Hindi and Urdu, reflecting the community’s historical involvement in the production and distribution of milk and dairy products.
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

                <!-- <a href="/about" class="inline-flex items-center px-4 py-2 border border-green-600 text-green-700 hover:bg-green-600 hover:text-white transition rounded">
                    Know More
                    <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L13.586 11H4a1 1 0 110-2h9.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a> -->
            </div>

            <!-- Right: Tight, visually-balanced framed grid -->
            <div class="grid grid-cols-2 gap-4 auto-rows-[1fr]">
                <div class="rounded-md overflow-hidden group relative row-span-1">
                    <img src="{{ asset('storage/about/4.png') }}" alt="Image 1"
                        class="w-full h-full object-cover aspect-[4/3] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105">
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-2">
                    <img src="{{ asset('storage/about/1.png') }}" alt="Image 2"
                        class="w-full h-full object-cover aspect-[3/4] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105">
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-2">
                    <img src="{{ asset('storage/about/2.png') }}" alt="Image 3"
                        class="w-full h-full object-cover aspect-[3/4] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105">
                </div>
                <div class="rounded-md overflow-hidden group relative row-span-1">
                    <img src="{{ asset('storage/about/3.png') }}" alt="Image 4"
                        class="w-full h-full object-cover aspect-[4/3] transform transition duration-500 group-hover:scale-105 group-hover:brightness-105">
                </div>
            </div>

        </div>
    </section>

    <!-- Merged History and Culture Section -->
    <section style="background-color: white; padding: 60px 20px;">
        <div style="max-width: 1100px; margin: 0 auto;">
            <!-- History -->
            <h2 style="font-size: 26px; font-weight: 700; text-align: center; margin-bottom: 24px;">History Of Community</h2>
            <p style="font-size: 16px; color: #374151; line-height: 1.7; margin-bottom: 16px;">
                Our Muslim community is a close-knit and vibrant group rooted in faith, tradition, and unity. Guided by the principles of Islam, we strive to foster a strong sense of brotherhood, compassion, and mutual respect among all members. We are committed to preserving our rich cultural and spiritual heritage while promoting education, social welfare, and community development.
            </p>
            <p style="font-size: 16px; color: #374151; line-height: 1.7; margin-bottom: 40px;">
                Through regular prayers, religious events, charitable activities, and community gatherings, we aim to create an inclusive and supportive environment for individuals and families. Our community welcomes everyone with open arms and encourages active participation in building a harmonious and progressive society rooted in Islamic values.
            </p>

            <!-- Culture Row -->
            <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: flex-start;">
                <!-- Image Left -->
                <div style="flex: 1; min-width: 280px;">
                    <img src="{{ asset('storage/about/2.png') }}" alt="Culture Image" style="width: 100%; border-radius: 8px; object-fit: cover;" />
                </div>

                <!-- Text Right -->
                <div style="flex: 2; min-width: 300px;">
                    <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px;">Our Culture</h3>
                    <p style="font-size: 16px; color: #374151; line-height: 1.7; margin-bottom: 16px;">
                        Through regular prayers, religious events, charitable activities, and community gatherings, we aim to create an inclusive and supportive environment for individuals and families. Our community welcomes everyone with open arms and encourages active participation in building a harmonious and progressive society rooted in Islamic values.
                    </p>
                    <p style="font-size: 16px; color: #374151; line-height: 1.7;">
                        Rich in heritage and tradition, our Muslim community reflects the beauty of Islamic culture and values. From festive celebrations like Eid to weekly Jum'ah prayers, we cherish and uphold the customs passed down through generations. We embrace diversity within our faith and strive to maintain a welcoming space for all backgrounds and age groups.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Members -->
    <section class="bg-white py-20 text-center">
        <h2 class="text-2xl md:text-3xl font-bold mb-14 text-gray-900">
            Our Respected Members
        </h2>

        <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-10 gap-y-16 px-6">
            @foreach ($communityMembers as $member)
            <div>
                <div class="overflow-hidden rounded-md shadow-sm">
                    <img
                        src="{{ asset('storage/' . $member->image) }}"
                        alt="{{ $member->name }}"
                        class="w-full h-[390px] object-cover
                   transform transition-transform duration-300
                   hover:scale-105" />
                </div>
                <h3 class="mt-4 text-[15px] font-semibold text-gray-900">
                    {{ $member->name }}
                </h3>
                <p class="text-sm text-gray-600">
                    {{ $member->designation }}
                </p>
            </div>
            @endforeach
        </div>
    </section>


    <section style="position: relative; width: 100%; height: 600px; background-image: url('{{ asset('storage/banner/about_us_banner.png') }}'); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center;">


        <!-- Centered Text -->
        <div style="position: relative; text-align: center; color: white; padding: 0 20px; margin-top: 320px;">
            <h1 style="font-size: 32px; font-weight: 600; line-height: 1.3; letter-spacing: 0.5px; ">
                Together in faith,<br>united in purpose.
            </h1>
            <p style="font-size: 18px; line-height: 1.6; max-width: 600px; margin: 0 auto; letter-spacing: 0.5px;">
                Our community welcomes everyone with open arms and<br>
                encourages active participation.
            </p>
        </div>
    </section>

</x-app-layout>