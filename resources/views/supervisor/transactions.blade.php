<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rekap Transaksi Siswa') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('transactions.export.all') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-download mr-2"></i>Export Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filter Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Transaksi</h3>
                    
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Siswa</label>
                            <select name="student_id" id="student_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Siswa</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->user_id }}">
                                        {{ $student->full_name }} ({{ $student->student_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipe Transaksi</label>
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Tipe</option>
                                <option value="topup">Top Up</option>
                                <option value="purchase">Pembelian</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                            <input type="date" name="date_from" id="date_from" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="lg:col-span-4 flex space-x-2">
                            <button type="button" id="filter-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                            <button type="button" id="reset-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-times mr-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" id="statistics-cards">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-full">
                                <i class="fas fa-receipt text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-transactions">{{ number_format($stats['total_transactions']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-full">
                                <i class="fas fa-arrow-up text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Isi Saldo</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-topup">
                                    Rp {{ number_format($stats['total_topup']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-full">
                                <i class="fas fa-shopping-cart text-red-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Pembelian</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-purchase">
                                    Rp {{ number_format($stats['total_purchase']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-full">
                                <i class="fas fa-users text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Siswa Aktif</p>
                                <p class="text-2xl font-semibold text-gray-900" id="active-students">{{ $stats['active_students'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Data Transaksi</h3>
                        <div class="flex items-center space-x-2">
                            <div class="text-sm text-gray-500" id="table-info">
                                </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto" id="transactions-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Deskripsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

    <script>
        let transactionsTable;

        $(document).ready(function() {
            // Initialize DataTable
            transactionsTable = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("supervisor.transactions") }}',
                    data: function(d) {
                        d.student_id = $('#student_id').val();
                        d.type = $('#type').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    {
                        data: 'date_formatted',
                        name: 'created_at',
                        className: 'text-sm text-gray-900'
                    },
                    {
                        data: 'student_info',
                        name: 'user.full_name',
                        render: function(data, type, row) {
                            return '<div><div class="text-sm font-medium text-gray-900">' + data.name + '</div>' +
                                   (data.student_id ? '<div class="text-sm text-gray-500">' + data.student_id + '</div>' : '') + '</div>';
                        }
                    },
                    {
                        data: 'type_badge',
                        name: 'type',
                        orderable: false
                    },
                    {
                        data: 'formatted_amount',
                        name: 'amount',
                        className: 'text-sm font-medium',
                        render: function(data, type, row) {
                            // Menggunakan Math.abs() untuk menghilangkan tanda minus dari 'row.amount'
                            const absoluteAmount = Math.abs(row.amount);
                            // Memformat angka menjadi format mata uang Indonesia (IDR)
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(absoluteAmount);
                        }
                    },
                    {
                        data: 'description_short',
                        name: 'description',
                        className: 'text-sm text-gray-500 max-w-xs',
                        render: function(data, type, row) {
                            if (type === 'display' && data && data.length > 50) {
                                return '<span title="' + row.description + '">' + data + '</span>';
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    processing: '<div class="flex items-center space-x-2"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div><span>Memuat data...</span></div>',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data per halaman',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    },
                    emptyTable: 'Tidak ada transaksi ditemukan'
                },
                dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"mb-2 sm:mb-0"f>>rt<"flex flex-col sm:flex-row sm:items-center sm:justify-between mt-4"<"mb-2 sm:mb-0"i><"mb-2 sm:mb-0"p>>',
                drawCallback: function(settings) {
                    // Update statistics after table draw
                    updateStatistics();
                }
            });

            // Filter handlers
            $('#filter-btn').on('click', function() {
                transactionsTable.ajax.reload();
                updateStatistics();
            });

            $('#reset-btn').on('click', function() {
                $('#filter-form')[0].reset();
                transactionsTable.ajax.reload();
                updateStatistics();
            });

            // Auto-filter on change for better UX
            $('#student_id, #type, #date_from, #date_to').on('change', function() {
                transactionsTable.ajax.reload();
                updateStatistics();
            });
        });

        // Update statistics via AJAX
        function updateStatistics() {
            const filters = {
                student_id: $('#student_id').val(),
                type: $('#type').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val()
            };

            $.ajax({
                url: '{{ route("supervisor.transactions.statistics") }}',
                method: 'GET',
                data: filters,
                paid: function(response) {
                    $('#total-transactions').text(new Intl.NumberFormat('id-ID').format(response.total_transactions));
                    $('#total-topup').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.total_topup));
                    $('#total-purchase').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.total_purchase));
                    $('#active-students').text(new Intl.NumberFormat('id-ID').format(response.active_students));
                },
                error: function() {
                    console.error('Failed to update statistics');
                }
            });
        }

        // ... rest of the script remains the same ...
    </script>
    
     <style>
/* Fix tampilan DataTables length menu */
.dataTables_length {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dataTables_length select {
    min-width: 60px;
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
}

.dataTables_length label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #374151;
    white-space: nowrap;
}
</style>
</x-app-layout>