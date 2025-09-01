<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Welcome Section with Animation --}}
            <div class="bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-700 text-white rounded-2xl shadow-2xl mb-8 p-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="absolute top-4 right-4 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-4 left-4 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-3xl font-bold mb-2">Panel Admin</h3>
                            <p class="text-lg opacity-90">Kelola seluruh sistem e-commerce dengan mudah</p>
                            <div class="mt-4 flex items-center space-x-6">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                    <span class="text-sm">System Online</span>
                                </div>
                                <div class="text-sm opacity-75">
                                    Last updated: {{ now()->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm opacity-75 mb-1">Total Revenue Bulan Ini</div>
                            <div class="text-4xl font-bold">Rp {{ number_format($data['monthly_revenue'], 0, ',', '.') }}</div>
                            <div class="text-sm mt-2 bg-green-500/20 px-3 py-1 rounded-full inline-block">
                                <span class="text-green-200">↑ +12.5% from last month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enhanced Statistics Overview --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-2xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($data['total_users']) }}</dd>
                                <div class="text-sm text-green-600 dark:text-green-400 mt-1">+5.2% this week</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 rounded-2xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total gurus</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($data['total_gurus']) }}</dd>
                                <div class="text-sm text-green-600 dark:text-green-400 mt-1">+8.1% this week</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-4 rounded-2xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($data['total_products']) }}</dd>
                                <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">+15.3% this week</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-4 rounded-2xl shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($data['total_orders']) }}</dd>
                                <div class="text-sm text-green-600 dark:text-green-400 mt-1">+23.4% this week</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enhanced Admin Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Admin Control Panel</h4>
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Quick Access
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    <a href="{{ route('admin.users.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-blue-600 dark:group-hover:text-blue-400">Kelola Users</span>
                    </a>

                    <a href="{{ route('guru_profiles.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-green-400 dark:hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-green-600 dark:group-hover:text-green-400">Verifikasi guru</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-yellow-400 dark:hover:border-yellow-500 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-yellow-600 dark:group-hover:text-yellow-400">Product Management</span>
                    </a>

                    <a href="{{ route('orders.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-purple-400 dark:hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-purple-600 dark:group-hover:text-purple-400">Order Management</span>
                    </a>

                    <a href="{{ route('categories.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Categories</span>
                    </a>

                    <a href="{{ route('shop.index') }}" class="group flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-red-400 dark:hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-300 hover:scale-105">
                        <div class="bg-gradient-to-r from-red-500 to-red-600 p-4 rounded-2xl group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <span class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-center group-hover:text-red-600 dark:group-hover:text-red-400">Preview Store</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- Enhanced User Registration Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">User Analytics</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pendaftaran 30 hari terakhir</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="userChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                {{-- Enhanced Pending guru Requests --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">guru Requests</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Permintaan yang perlu direview</p>
                        </div>
                        @if($data['pending_gurus'] > 0)
                            <span class="bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                                {{ $data['pending_gurus'] }} pending
                            </span>
                        @else
                            <span class="bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                All Clear
                            </span>
                        @endif
                    </div>
                    
                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($data['pending_guru_requests'] as $request)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-3 hover:shadow-md transition-all duration-200 last:mb-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $request->store_name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $request->user->name }} • {{ $request->created_at->format('d M Y') }}
                                        </p>
                                        <div class="mt-2">
                                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs px-2 py-1 rounded-full">
                                                Menunggu Verifikasi
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <form action="{{ route('guru_profiles.verify', $request->guru_profile_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-600 dark:text-green-400 p-2 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('guru_profiles.show', $request->guru_profile_id) }}" class="bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-600 dark:text-blue-400 p-2 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="bg-green-100 dark:bg-green-900/20 w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada permintaan guru yang menunggu</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Semua guru telah diverifikasi</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Enhanced Latest Users --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">Latest Users</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">User terbaru yang mendaftar</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            View All
                        </a>
                    </div>
                    
                    <div class="max-h-80 overflow-y-auto space-y-3">
                        @forelse ($data['latest_users'] as $user)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $user->email }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                {{ $user->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-3 py-1 text-xs rounded-full font-medium
                                            @if($user->role && $user->role->role_name == 'admin') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                                            @elseif($user->role && $user->role->role_name == 'guru') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                            @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                            @endif">
                                            {{ $user->role ? ucfirst($user->role->role_name) : 'User' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="bg-gray-100 dark:bg-gray-700/30 w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada user baru</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Enhanced Latest Orders --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100">Latest Orders</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pesanan terbaru masuk</p>
                        </div>
                        <a href="{{ route('orders.index') }}" class="bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            View All
                        </a>
                    </div>
                    
                    <div class="max-h-80 overflow-y-auto space-y-3">
                        @forelse ($data['latest_orders'] as $order)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                #{{ $order->order_id }}
                                            </p>
                                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                                @if($order->status == 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                                                @elseif($order->status == 'processing') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                                @elseif($order->status == 'shipped') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                                                @else bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                                @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                            {{ $order->user->name }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $order->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="bg-gray-100 dark:bg-gray-700/30 w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada pesanan</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pesanan akan muncul di sini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Chart.js Script --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Enhanced User Registration Chart
        const ctx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($data['user_signups']->pluck('date')->map(function($date) { return date('d M', strtotime($date)); })),
                datasets: [{
                    label: 'Pendaftaran User',
                    data: @json($data['user_signups']->pluck('count')),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#1d4ed8',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        callbacks: {
                            title: function(context) {
                                return 'Tanggal: ' + context[0].label;
                            },
                            label: function(context) {
                                return 'Pendaftaran: ' + context.parsed.y + ' user';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(107, 114, 128, 0.1)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            stepSize: 1,
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return value + ' user';
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        capBezierPoints: true
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics cards on page load
            const statCards = document.querySelectorAll('.hover\\:shadow-2xl');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });

            // Add hover effects to action buttons
            const actionButtons = document.querySelectorAll('.group');
            actionButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>

    <style>
        /* Custom scrollbar for overflow areas */
        .max-h-80::-webkit-scrollbar {
            width: 6px;
        }
        .max-h-80::-webkit-scrollbar-track {
            background: transparent;
        }
        .max-h-80::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        .max-h-80::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.7);
        }

        /* Enhance animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Glassmorphism effect */
        .backdrop-blur-sm {
            backdrop-filter: blur(4px);
        }
    </style>
</x-app-layout>