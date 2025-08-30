<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Keuntungan Koperasi') }}
            </h2>
            <div class="flex space-x-2">
                {{-- Tombol ini akan memanggil fungsi JavaScript di bawah --}}
                <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    Export Excel
                </button>
                <button onclick="printReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Print Laporan
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Laporan</h3>
                    <form method="GET" action="{{ route('supervisor.profit') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <!-- Period Selection -->
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <select name="period" id="period_select" onchange="handlePeriodChange()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="custom" {{ request('period') == 'custom' || (!request('period') && (request('start_date') || request('end_date'))) ? 'selected' : '' }}>Tanggal Pilihan</option>
                                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                                <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                                <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                                <option value="last_week" {{ request('period') == 'last_week' ? 'selected' : '' }}>Minggu Lalu</option>
                                <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                                <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                                <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                            </select>
                        </div>

                        <!-- Custom Dates - Side by Side -->
                        <div id="custom_dates" class="md:col-span-4 {{ request('period') && request('period') != 'custom' ? 'hidden' : '' }}">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                                    <input type="date" name="start_date" 
                                           value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                                    <input type="date" name="end_date" 
                                           value="{{ request('end_date', now()->format('Y-m-d')) }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Seller Selection (for Guru only) -->
                        @if(auth()->user()->role === 'guru')
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Penjual</label>
                            <select name="admin_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Penjual</option>
                                @foreach($admins as $admin)
                                <option value="{{ $admin->user_id }}" {{ request('admin_id') == $admin->user_id ? 'selected' : '' }}>
                                    {{ $admin->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="md:col-span-2 flex gap-2">
                            <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                                Filter
                            </button>

                            <a href="{{ route('supervisor.profit.reset') }}?period=custom&start_date=&end_date=" 
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-3 rounded-md transition-colors duration-200 flex items-center justify-center">
                               Reset 
                            </a>
                        </div>
                    </form>
                </div>
            </div>

               <div class="container mx-auto px-4 py-6">        
        <!-- Summary Cards - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
            <!-- Total Pendapatan Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-4 md:p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-3 md:ml-4 min-w-0 flex-1">
                            <p class="text-xs md:text-sm font-medium text-gray-500 truncate">Total Pendapatan</p>
                            <p class="text-lg md:text-2xl font-bold text-gray-900 truncate" title="Rp 15.750.000">
                                Rp 15.750.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Modal Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-4 md:p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 md:ml-4 min-w-0 flex-1">
                            <p class="text-xs md:text-sm font-medium text-gray-500 truncate">Total Modal</p>
                            <p class="text-lg md:text-2xl font-bold text-gray-900 truncate" title="Rp 12.500.000">
                                Rp 12.500.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Keuntungan Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-4 md:p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-3 md:ml-4 min-w-0 flex-1">
                            <p class="text-xs md:text-sm font-medium text-gray-500 truncate">Total Keuntungan</p>
                            <p class="text-lg md:text-2xl font-bold text-green-600 truncate" title="Rp 3.250.000">
                                Rp 3.250.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Margin Keuntungan Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-4 md:p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 md:ml-4 min-w-0 flex-1">
                            <p class="text-xs md:text-sm font-medium text-gray-500 truncate">Margin Keuntungan</p>
                            <p class="text-lg md:text-2xl font-bold text-green-600 truncate" title="20.6%">
                                20.6%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Charts Section - Made smaller -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Daily Sales Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Grafik Penjualan Harian</h3>
                        <div class="h-48">
                            <canvas id="dailySalesChart" class="max-h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Profit Margin Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Perbandingan Pendapatan vs Keuntungan</h3>
                        <div class="h-48">
                            <canvas id="profitChart" class="max-h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products by Profit -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Produk Teratas berdasarkan Keuntungan</h3>
                    <div class="overflow-x-auto">
                        <table id="products-table" class="min-w-full divide-y divide-gray-200" style="width:100%">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Modal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Modal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($product_stats ?? collect() as $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($stat['product']->image)
                                            <img class="h-10 w-10 rounded-full object-cover mr-3" src="{{ asset('storage/' . $stat['product']->image) }}" alt="{{ $stat['product']->name }}">
                                            @else
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $stat['product']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $stat['product']->category->name ?? 'Tanpa Kategori' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['quantity_sold'] ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['product']->formatted_price ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['product']->formatted_modal_price ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($stat['revenue'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($stat['modal_cost'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($stat['profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                        Rp {{ number_format($stat['profit'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $margin = $stat['profit_margin'] ?? 0; @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $margin >= 20 ? 'bg-green-100 text-green-800' : ($margin >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($margin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        Tidak ada data produk dalam periode ini
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(auth()->user()->role === 'guru')
            <!-- Seller Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Performa Penjual</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penjual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($admin_stats ?? collect() as $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                                <span class="text-sm font-medium text-blue-600">{{ substr($stat['admin']->full_name, 0, 2) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $stat['admin']->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $stat['admin']->student_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['orders_count'] ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($stat['revenue'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($stat['modal_cost'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($stat['profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                        Rp {{ number_format($stat['profit'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $margin = $stat['profit_margin'] ?? 0; @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $margin >= 20 ? 'bg-green-100 text-green-800' : ($margin >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($margin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        Tidak ada data penjual dalam periode ini
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize period change handler on page load
    handlePeriodChange();
    
    // Initialize charts
    initializeCharts();
    
    // Auto refresh every 5 minutes
    setInterval(() => {
        location.reload();
    }, 300000);
});

// --- DataTables Initialization ---
$(function () {
    // Products Table Initialization
    $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('supervisor.profit.product_data') }}",
            data: function (d) {
                d.period = $('select[name="period"]').val();
                d.start_date = $('input[name="start_date"]').val();
                d.end_date = $('input[name="end_date"]').val();
                @if(auth()->user()->role === 'guru')
                d.admin_id = $('select[name="admin_id"]').val();
                @endif
            },
            error: function(xhr, error, code) {
                console.error('DataTables Ajax error:', xhr.responseText);
                alert('Error loading data: ' + xhr.responseText);
            }
        },
        columns: [
            { 
                data: 'product_info', 
                name: 'products.name', 
                orderable: false, // Set to false since it's HTML content
                searchable: true 
            },
            { 
                data: 'quantity_sold', 
                name: 'quantity_sold', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'sell_price', 
                name: 'products.price', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'modal_price_used', 
                name: 'modal_price_used', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'revenue', 
                name: 'revenue', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'total_modal_cost', 
                name: 'total_modal_cost', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'profit', 
                name: 'profit', 
                searchable: false,
                orderable: true
            },
            { 
                data: 'profit_margin', 
                name: 'profit_margin', 
                orderable: false, 
                searchable: false 
            }
        ],
        order: [[6, 'desc']], // Order by profit column
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: 'Bfrtip',
        responsive: true,
        autoWidth: false
    });

    // Admin/Sellers Table (only initialize if it exists)
    if ($('#sellers-table').length) {
        $('#sellers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('supervisor.profit.admin_data') }}",
                data: function (d) {
                    d.period = $('select[name="period"]').val();
                    d.start_date = $('input[name="start_date"]').val();
                    d.end_date = $('input[name="end_date"]').val();
                    @if(auth()->user()->role === 'guru')
                    d.admin_id = $('select[name="admin_id"]').val();
                    @endif
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax error:', xhr.responseText);
                    alert('Error loading data: ' + xhr.responseText);
                }
            },
            columns: [
                { 
                    data: 'seller_info', 
                    name: 'users.full_name',
                    orderable: false,
                    searchable: true
                },
                { 
                    data: 'orders_count', 
                    name: 'orders_count', 
                    searchable: false,
                    orderable: true
                },
                { 
                    data: 'revenue', 
                    name: 'revenue', 
                    searchable: false,
                    orderable: true
                },
                { 
                    data: 'modal_cost', 
                    name: 'modal_cost', 
                    searchable: false,
                    orderable: true
                },
                { 
                    data: 'profit', 
                    name: 'profit', 
                    searchable: false,
                    orderable: true
                },
                { 
                    data: 'profit_margin', 
                    name: 'profit_margin', 
                    orderable: false, 
                    searchable: false 
                }
            ],
            order: [[4, 'desc']], // Order by profit column
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: 'Bfrtip',
            responsive: true,
            autoWidth: false
        });
    }

    // Reload DataTables when filter form is submitted
    $('#filter-form, form').on('submit', function() {
        $('#products-table').DataTable().ajax.reload();
        if ($('#sellers-table').length) {
            $('#sellers-table').DataTable().ajax.reload();
        }
    });
});

// --- Chart Initialization ---
function initializeCharts() {
    // Daily Sales Chart
    const dailySalesCtx = document.getElementById('dailySalesChart');
    if (dailySalesCtx) {
        const dailySalesData = @json($daily_sales ?? []);
        new Chart(dailySalesCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: Object.keys(dailySalesData),
                datasets: [{
                    label: 'Pendapatan',
                    data: Object.values(dailySalesData).map(item => item.revenue || 0),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Keuntungan',
                    data: Object.values(dailySalesData).map(item => item.profit || 0),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    // Profit Chart
    const profitCtx = document.getElementById('profitChart');
    if (profitCtx) {
        new Chart(profitCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Modal', 'Keuntungan'],
                datasets: [{
                    data: [{{ $summary['total_modal_cost'] ?? 0 }}, {{ $summary['total_profit'] ?? 0 }}],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderColor: [
                        'rgba(239, 68, 68, 1)',
                        'rgba(34, 197, 94, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}

// --- Helper Functions ---

// Function to handle period dropdown change
function handlePeriodChange() {
    const periodSelect = document.getElementById('period_select');
    const customDates = document.getElementById('custom_dates');
    
    if (periodSelect && customDates) {
        const period = periodSelect.value;
        
        if (period === 'custom') {
            customDates.classList.remove('hidden');
            customDates.style.display = 'block';
        } else {
            customDates.classList.add('hidden');
            customDates.style.display = 'none';
        }
    }
}

// Function to build URL with current filter parameters
function buildUrlWithFilters(baseUrl) {
    const form = document.getElementById('filter-form');
    if (!form) return baseUrl;
    
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value) { // Hanya tambahkan parameter yang memiliki nilai
            params.append(key, value);
        }
    }
    return `${baseUrl}?${params.toString()}`;
}

// Reset filter function
function resetFilter() {
    // Reset period to default (this_month)
    const periodSelect = document.getElementById('period_select');
    if (periodSelect) {
        periodSelect.value = 'this_month';
    }
    
    // Reset custom dates to default values
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (startDate) {
        startDate.value = firstDayOfMonth.toISOString().split('T')[0];
    }
    if (endDate) {
        endDate.value = today.toISOString().split('T')[0];
    }
    
    // Reset admin selector if exists
    const adminSelect = document.getElementById('admin_select');
    if (adminSelect) {
        adminSelect.value = '';
    }
    
    // Hide custom dates section
    const customDates = document.getElementById('custom_dates');
    if (customDates) {
        customDates.classList.add('hidden');
        customDates.style.display = 'none';
    }
    
    // Redirect to clear URL parameters
    window.location.href = "{{ route('supervisor.profit') }}";
}

// --- Export and Print Functions ---

/**
 * Mengarahkan ke rute ekspor Excel dengan membawa parameter filter saat ini.
 * Pastikan route 'profit.exportExcel' sudah ada di web.php
 */
function exportReport() {
    const url = buildUrlWithFilters("{{ route('profit.exportExcel') }}");
    window.location.href = url;
}

/**
 * Membuka tab baru ke rute cetak dengan membawa parameter filter saat ini.
 * Pastikan route 'profit.print' sudah ada di web.php
 */
function printReport() {
    const url = buildUrlWithFilters("{{ route('profit.print') }}");
    window.open(url, '_blank');
}

/**
 * Alternative print function for simple window.print()
 */
function printReportSimple() {
    window.print();
}
</script>

    <style>
        /* Custom responsive adjustments */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .text-lg {
                font-size: 1rem;
                line-height: 1.5rem;
            }
            .text-2xl {
                font-size: 1.25rem;
                line-height: 1.75rem;
            }
        }

        @media print {
            .no-print {
                display: none;
            }
            
            body {
                -webkit-print-color-adjust: exact;
            }
        }
/* DataTables Custom Styling */
.dataTable {
    border-collapse: separate !important;
    border-spacing: 0;
}

.dataTable thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 16px 12px;
    color: #475569;
}

.dataTable tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f1f5f9;
}

.dataTable tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.dataTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border: none;
}

/* Product column styling */
.dataTable tbody tr td:first-child {
    min-width: 280px;
    max-width: 300px;
}

/* Quantity column - center alignment */
.dataTable tbody tr td:nth-child(2) {
    text-align: center;
    min-width: 100px;
}

/* Price columns - right alignment */
.dataTable tbody tr td:nth-child(3),
.dataTable tbody tr td:nth-child(4),
.dataTable tbody tr td:nth-child(5),
.dataTable tbody tr td:nth-child(6),
.dataTable tbody tr td:nth-child(7) {
    text-align: right;
    min-width: 120px;
}

/* Margin column - center alignment */
.dataTable tbody tr td:last-child {
    text-align: center;
    min-width: 80px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dataTable {
        font-size: 0.875rem;
    }
    
    .dataTable tbody tr td:first-child {
        min-width: 200px;
    }
    
    .dataTable tbody tr td {
        padding: 8px;
    }
}

/* DataTables wrapper improvements */
.dataTables_wrapper {
    margin-top: 1rem;
}

.dataTables_length,
.dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_info,
.dataTables_paginate {
    margin-top: 1rem;
}

/* Search box styling */
.dataTables_filter input {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.dataTables_filter input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Pagination styling */
.dataTables_paginate .paginate_button {
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}

.dataTables_paginate .paginate_button:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.dataTables_paginate .paginate_button.current {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Loading state */
.dataTables_processing {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    font-weight: 500;
    color: #6b7280;
}

/* Badge improvements */
.badge-category {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.quantity-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 50px;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.profit-indicator {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 700;
}

.margin-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid;
}
</style>
</x-app-layout>