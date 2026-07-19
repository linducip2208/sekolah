{{-- Widget: Contact Section --}}
<section id="contact" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            @if($section->title)
                <h2 class="text-3xl font-display font-bold text-gray-900 mb-2 text-center">{{ $section->title }}</h2>
            @endif
            @if($section->subtitle)
                <p class="text-lg text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
            @endif
            <form action="{{ url('/s/' . $school->subdomain . '/kontak') }}" method="POST" class="space-y-5">
                @csrf
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="email@anda.com">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon (opsional)</label>
                    <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="08xx-xxxx-xxxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                    <textarea name="message" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Tulis pesan Anda..."></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="font-semibold px-8 py-3 rounded-lg text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5"
                        style="background: {{ $branding['colors']['primary'] ?? '#2563EB' }}">
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
