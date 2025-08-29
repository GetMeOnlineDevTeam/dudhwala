<x-app-layout>

    @foreach($venues as $index => $venue)

    @php
    $coverImage = $venue->images->firstWhere('is_cover', true);
    $otherImages = $venue->images->where('is_cover', false)->values();
    @endphp

    <section class="bg-white py-16 border-b border-gray-200">
        <div class="container mx-auto px-4">
            {{-- Title --}}
            <h2 class="text-center text-3xl font-semibold mb-10">
                Venue {{ $index + 1 }}: {{ $venue->name }}
            </h2>

            {{-- Image Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Top 3 --}}
                @foreach($otherImages->slice(0, 3) as $img)
                <div class="overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $img->image) }}"
                        alt="Venue image"
                        class="w-full h-44 object-cover
                                   transform transition-transform duration-300
                                   hover:scale-105" />
                </div>
                @endforeach

                {{-- Cover Image --}}
                @if($coverImage)
                <div class="relative col-span-1 md:col-span-3 overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $coverImage->image) }}"
                        alt="Cover image"
                        class="w-full h-auto object-cover
                                   transform transition-transform duration-300
                                   hover:scale-105" />
                    <div style="font-size: 2em;" class="absolute bottom-0 right-0
                                    bg-green-800 text-white text-lg font-semibold
                                    px-4 py-2 rounded-tl-md">
                        {{ $venue->name }}
                    </div>
                </div>
                @endif

                {{-- Bottom 3 --}}
                @foreach($otherImages->slice(3, 3) as $img)
                <div class="overflow-hidden rounded-lg">
                    <img
                        src="{{ asset('storage/' . $img->image) }}"
                        alt="Venue image"
                        class="w-full h-44 object-cover
                                   transform transition-transform duration-300
                                   hover:scale-105" />
                </div>
                @endforeach
            </div>

            {{-- Address & Contact --}}
            @if($venue->address)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12 max-w-2xl mx-auto">
                {{-- Location --}}
                <div>
                    <h3 class="text-4xl font-bold mb-4">Location</h3>
                    <div class="flex items-start gap-3 text-black-700 mb-6">
                        <svg
                            class="w-6 h-6 mt-1 text-black-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M10 2a5 5 0 00-5 5c0 5 5 11 5 11s5-6 5-11a5 5 0 00-5-5zm0 7a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>

                        <span>{{ $venue->address->addr }}</span>
                    </div>
                    <a href="{{ route('book.hall') }}"
                        class="inline-flex items-center bg-green-800 text-white text-sm font-semibold
                                  px-4 py-2 rounded transition hover:bg-green-700">
                        Check Availability
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                {{-- Contact --}}
                <div class="text-right">
                    <h3 class="text-4xl font-bold mb-4">Contact</h3>

                    <div class="space-y-4 text-gray-700 mb-6">
                        @foreach($activePhoneContacts as $contact)
                        <div class="flex items-center justify-end gap-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-black-500"
                                fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 
           1 0 011.11-.21 11.745 11.745 0 003.64.73 1 
           1 0 011 1V20a1 1 0 01-1 1A16.001 16.001 0 012 
           4a1 1 0 011-1h3.5a1 1 0 011 1 11.745 
           11.745 0 00.73 3.64 1 1 0 01-.21 
           1.11l-2.2 2.2z" />
                            </svg>

                            <span>{{ $contact->contact }}</span>
                        </div>
                        @endforeach
                    </div>

                    <a
                        href="{{ $venue->address->google_link ?? '#' }}"
                        target="_blank"
                        class="inline-flex items-center justify-end border border-green-800 text-green-800 text-sm font-semibold
               px-4 py-2 rounded transition hover:bg-green-100">
                        <span class="flex items-center gap-2">
                            Google Maps
                            <img
                                src="{{ asset('storage/icons/google-maps.svg') }}"
                                alt="Google Maps"
                                class="w-5 h-5 object-contain" />
                        </span>
                    </a>
                </div>

            </div>
            @endif
        </div>
    </section>

    @endforeach

</x-app-layout>