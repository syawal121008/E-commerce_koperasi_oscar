<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tulis Ulasan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 p-4 rounded">
                        <ul class="list-disc ml-6">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Pilih Produk -->
                    <div class="mb-4">
                        <label class="block font-semibold text-gray-700 mb-2">Produk</label>
                        <select name="product_id" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($products as $product)
                                <option value="{{ $product->product_id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rating -->
                    <div class="mb-4">
                        <label class="block font-semibold text-gray-700 mb-2">Rating</label>
                        <div class="flex items-center space-x-1 text-yellow-400 text-2xl cursor-pointer" id="rating-stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="far fa-star" data-value="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="0">
                    </div>

                    <!-- Komentar -->
                    <div class="mb-4">
                        <label class="block font-semibold text-gray-700 mb-2">Komentar</label>
                        <textarea name="comment" rows="4" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tulis pengalaman kamu..."></textarea>
                    </div>

                    <!-- Upload Foto -->
                    <div class="mb-4">
                        <label class="block font-semibold text-gray-700 mb-2">Foto (Opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="block w-full text-sm text-gray-500 border rounded-lg">
                        <img id="photo-preview" class="mt-2 w-32 h-32 object-cover rounded hidden">
                    </div>

                    <!-- Upload Video -->
                    <div class="mb-4">
                        <label class="block font-semibold text-gray-700 mb-2">Video (Opsional)</label>
                        <input type="file" name="video" accept="video/*" class="block w-full text-sm text-gray-500 border rounded-lg">
                        <video id="video-preview" class="mt-2 w-64 rounded hidden" controls></video>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Simpan Ulasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Rating Bintang
        const stars = document.querySelectorAll('#rating-stars i');
        const ratingValue = document.getElementById('rating-value');
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.getAttribute('data-value');
                ratingValue.value = value;

                stars.forEach((s, index) => {
                    if (index < value) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
        });

        // Preview Foto
        document.querySelector('input[name="photo"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('photo-preview');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        });

        // Preview Video
        document.querySelector('input[name="video"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('video-preview');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>
