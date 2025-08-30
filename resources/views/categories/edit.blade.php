<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Kategori') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 text-red-600">
                        <strong>Whoops!</strong> Ada masalah dengan input Anda.<br><br>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('categories.update', $category->category_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-bold mb-2">
                            Nama Kategori:
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $category->name) }}"
                               class="form-input w-full rounded-md shadow-sm border-gray-300"
                               placeholder="Contoh: Pakaian Pria">
                    </div>

                    <div class="mt-4 flex items-center space-x-2">
                        <a class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600" 
                           href="{{ route('categories.index') }}">
                            Kembali
                        </a>
                        <button type="submit" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Perbarui
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
