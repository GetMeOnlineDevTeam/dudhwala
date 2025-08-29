<x-app-layout>

    @php
    // Assume $mapLink is passed from your controller, e.g.:
    // $mapLink = $venue->address->google_map_link;
    @endphp

    @php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    @endphp

    <!-- Google Map Embed -->
    <section class="w-full h-[400px]">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7382.710497912662!2d73.20359358786855!3d22.302400595032797!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395fcf5e8be59929%3A0xdc2382659ae064f!2sDudhwala%20Hall!5e0!3m2!1sen!2sin!4v1753446416643!5m2!1sen!2sin"
            class="w-full h-full border-0"
            allowfullscreen
            loading="lazy">
        </iframe>
    </section>



    <!-- Contact Form -->
    <section class="bg-white py-16">
        <div class="max-w-2xl mx-auto px-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-center mb-8">
                Send us a Message
            </h2>

            @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: @json(session('success')),
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                    });
                });
            </script>
            @endif


            <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div> 
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input
                        type="text"
                        name="first_name"
                        id="first_name"
                        value="{{ old('first_name', $user->first_name ?? '') }}"
                        class="mt-1 block w-full border {{ $errors->has('first_name') ? 'border-red-600' : 'border-gray-300' }} rounded-md px-3 py-2 focus:ring-green-600 focus:border-green-600" />
                    </div>
                    
                    <!-- Last Name -->
                     <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input
                        type="text"
                        name="last_name"
                        id="last_name"
                        value="{{ old('last_name', $user->last_name ?? '') }}"
                        class="mt-1 block w-full border {{ $errors->has('last_name') ? 'border-red-600' : 'border-gray-300' }} rounded-md px-3 py-2 focus:ring-green-600 focus:border-green-600" />
                    </div>
                    
                    <!-- Phone Number (prefill from contact_number) -->
                    <div> 
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input
                        type="text"
                        name="phone_no"
                        id="phone_no"
                        value="{{ old('phone_no', $user->contact_number ?? '') }}"
                        class="mt-1 block w-full border {{ $errors->has('phone_no') ? 'border-red-600' : 'border-gray-300' }} rounded-md px-3 py-2 focus:ring-green-600 focus:border-green-600" />
                    </div>
                    

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input
                            type="text"
                            name="subject"
                            id="subject"
                            value="{{ old('subject') }}"
                            class="mt-1 block w-full border {{ $errors->has('subject') ? 'border-red-600' : 'border-gray-300' }} rounded-md px-3 py-2 focus:ring-green-600 focus:border-green-600" />
                        @error('subject')
                        <p class="mt-1 text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea
                        name="message"
                        id="message"
                        rows="5"
                        class="mt-1 block w-full border {{ $errors->has('message') ? 'border-red-600' : 'border-gray-300' }} rounded-md px-3 py-2 focus:ring-green-600 focus:border-green-600">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="mt-1 text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button
                        type="submit"
                        class="inline-block bg-green-800 text-white font-semibold px-8 py-3 rounded-md hover:bg-green-700 transition">
                        Send Message
                    </button>
                </div>
            </form>


        </div>
    </section>

</x-app-layout>