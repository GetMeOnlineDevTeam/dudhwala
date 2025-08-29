<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- Table of Contents -->
            <aside class="md:col-span-1 sticky top-24 self-start">
                <h2 class="text-lg font-semibold mb-4">Table of Contents</h2>
                <ul class="space-y-3 text-sm">
                    @foreach ($policies as $policy)
                        <li>
                            <a href="#{{ Str::slug($policy->title, '-') }}" class="text-gray-700 hover:text-green-700 transition">
                                {{ $policy->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <!-- Policy Content -->
            <div class="md:col-span-3 scroll-smooth">
                @foreach ($policies as $policy)
                    <section
                        id="{{ Str::slug($policy->title, '-') }}"
                        class="scroll-mt-28 pb-12"
                    >
                        <h2 class="text-2xl font-bold mb-3">{{ $policy->title }}</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($policy->text)) !!}
                        </div>

                        @if (!$loop->last)
                            <hr class="my-10 border-t border-gray-200" />
                        @endif
                    </section>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
