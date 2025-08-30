<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Review') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($reviews->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($reviews as $review)
                        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow flex flex-col">
                            <div class="flex items-center mb-2">
                                <div class="flex-1">
                                    <strong class="text-lg text-gray-800">{{ $review->user->name ?? 'User' }}</strong>
                                    <p class="text-sm text-gray-500">Produk: {{ $review->product->name ?? '-' }}</p>
                                </div>
                                <div class="text-yellow-500 text-xl">
                                    @for ($i = 0; $i < $review->rating; $i++)
                                        &#9733;
                                    @endfor
                                    @for ($i = $review->rating; $i < 5; $i++)
                                        &#9734;
                                    @endfor
                                </div>
                            </div>
                            
                            @if($review->comment)
                                <p class="text-gray-700 mb-3 line-clamp-3">{{ $review->comment }}</p>
                            @endif

                            @if($review->photo_path)
                                <img src="{{ asset('storage/' . $review->photo_path) }}" alt="Foto Review" class="w-full h-48 object-cover rounded mb-3" />
                            @endif

                            @if($review->video_path)
                                <video controls class="w-full rounded mb-3" preload="metadata">
                                    <source src="{{ asset('storage/' . $review->video_path) }}" type="video/mp4" />
                                    Your browser does not support the video tag.
                                </video>
                            @endif

                            <small class="text-gray-400 mt-auto">{{ $review->created_at->format('d M Y H:i') }}</small>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <i class="fas fa-comment-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum ada review</h3>
                    <p class="text-gray-600">Belum ada review yang tersedia untuk produk ini.</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-app-layout>
