<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rekap Isi Saldo Siswa') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="exportTopups()" 
                        id="export-btn"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filter Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Isi Saldo</h3>
                    
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Siswa</label>
                            <select name="student_id" id="student_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Siswa</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->user_id }}">
                                        {{ $student->full_name }} @if($student->student_id)({{ $student->student_id }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Berhasil</option>
                                <option value="failed">Gagal</option>
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
                            <button type="button" onclick="exportFilteredTopups()" 
                                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-download mr-2"></i>Export Filter
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
                                <i class="fas fa-plus-circle text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Isi Saldo</p>
                                <p class="text-2xl font-semibold text-gray-900" id="total-requests">{{ number_format($stats['total_requests']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-full">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Menunggu</p>
                                <p class="text-2xl font-semibold text-gray-900" id="pending-count">{{ number_format($stats['pending_count']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-full">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Berhasil</p>
                                <p class="text-2xl font-semibold text-gray-900" id="paid-amount">
                                    Rp {{ number_format($stats['paid_amount']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-full">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Gagal</p>
                                <p class="text-2xl font-semibold text-gray-900" id="failed-count">{{ number_format($stats['failed_count']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Topups Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Data Isi Saldo</h3>
                        <div class="flex items-center space-x-2">
                            <div class="text-sm text-gray-500" id="table-info">
                                <!-- Will be populated by DataTables -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto" id="topups-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Metode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Referensi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Modal for Export --}}
    <div id="loading-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Mengexport Data...</h3>
                    <p class="text-sm text-gray-500">Mohon tunggu sebentar</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    {{-- Approve Modal --}}
    <div x-data="{ showApproveModal: false, selectedTopupId: '' }" x-cloak>
        <div x-show="showApproveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Persetujuan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menyetujui permintaan top up ini?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmApprove()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Setujui
                        </button>
                        <button @click="showApproveModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-data="{ showRejectModal: false, selectedTopupId: '', rejectReason: '' }" x-cloak>
        <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-times text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Penolakan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-3">
                                        Apakah Anda yakin ingin menolak permintaan top up ini?
                                    </p>
                                    <div>
                                        <label for="reject-reason" class="block text-sm font-medium text-gray-700">Alasan Penolakan (Opsional)</label>
                                        <textarea x-model="rejectReason" id="reject-reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Masukkan alasan penolakan..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmReject()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Tolak
                        </button>
                        <button @click="showRejectModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- CSRF Token Meta --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        let topupsTable;

        $(document).ready(function() {
            // Initialize DataTable
            topupsTable = $('#topups-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("supervisor.topups") }}',
                    data: function(d) {
                        d.student_id = $('#student_id').val();
                        d.status = $('#status').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    {
                        data: 'date_formatted',
                        name: 'created_at',
                        render: function(data, type, row) {
                            return '<div><div>' + data.date + '</div><div class="text-xs text-gray-500">' + data.time + '</div></div>';
                        }
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
                        data: 'formatted_amount',
                        name: 'amount',
                        className: 'text-sm font-medium text-gray-900'
                    },
                    {
                        data: 'method_badge',
                        name: 'method',
                        orderable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false
                    },
                    {
                        data: 'reference_info',
                        name: 'payment_reference',
                        render: function(data, type, row) {
                            return '<div><div class="font-mono text-blue-600">' + data.payment_reference + '</div>' +
                                   '<div class="text-xs text-gray-500">#' + data.topup_id + '</div></div>';
                        },
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
                    emptyTable: 'Tidak ada data top up ditemukan'
                },
                dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"mb-2 sm:mb-0"f>>rt<"flex flex-col sm:flex-row sm:items-center sm:justify-between mt-4"<"mb-2 sm:mb-0"i><"mb-2 sm:mb-0"p>>',
                drawCallback: function(settings) {
                    // Update statistics after table draw
                    updateStatistics();
                }
            });

            // Filter handlers
            $('#filter-btn').on('click', function() {
                topupsTable.ajax.reload();
                updateStatistics();
            });

            $('#reset-btn').on('click', function() {
                $('#filter-form')[0].reset();
                topupsTable.ajax.reload();
                updateStatistics();
            });

            // Auto-filter on change for better UX
            $('#student_id, #status, #date_from, #date_to').on('change', function() {
                topupsTable.ajax.reload();
                updateStatistics();
            });
        });

        // Update statistics via AJAX
        function updateStatistics() {
            const filters = {
                student_id: $('#student_id').val(),
                status: $('#status').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val()
            };

            $.ajax({
                url: '{{ route("supervisor.topups.statistics") }}',
                method: 'GET',
                data: filters,
                paid: function(response) {
                    $('#total-requests').text(new Intl.NumberFormat('id-ID').format(response.total_requests));
                    $('#pending-count').text(new Intl.NumberFormat('id-ID').format(response.pending_count));
                    $('#paid-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.paid_amount));
                    $('#failed-count').text(new Intl.NumberFormat('id-ID').format(response.failed_count));
                },
                error: function() {
                    console.error('Failed to update statistics');
                }
            });
        }

        // Global variables for modals
        function approveTopup(topupId) {
            const approveComponent = document.querySelector('[x-data*="showApproveModal"]').__x.$data;
            approveComponent.selectedTopupId = topupId;
            approveComponent.showApproveModal = true;
        }

        function rejectTopup(topupId) {
            const rejectComponent = document.querySelector('[x-data*="showRejectModal"]').__x.$data;
            rejectComponent.selectedTopupId = topupId;
            rejectComponent.rejectReason = '';
            rejectComponent.showRejectModal = true;
        }

        function confirmApprove() {
            const component = document.querySelector('[x-data*="showApproveModal"]').__x.$data;
            const topupId = component.selectedTopupId;
            
            if (!topupId) return;

            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyetujui...';

            fetch(`/topup/${topupId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.paid) {
                    component.showApproveModal = false;
                    topupsTable.ajax.reload();
                    updateStatistics();
                    
                    // Show paid message
                    showNotification('Top up berhasil disetujui!', 'success');
                } else {
                    alert('Gagal menyetujui top up: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyetujui top up');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = 'Ya, Setujui';
            });
        }

        function confirmReject() {
            const component = document.querySelector('[x-data*="showRejectModal"]').__x.$data;
            const topupId = component.selectedTopupId;
            const reason = component.rejectReason;
            
            if (!topupId) return;

            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menolak...';

            fetch(`/topup/${topupId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.paid) {
                    component.showRejectModal = false;
                    topupsTable.ajax.reload();
                    updateStatistics();
                    
                    // Show paid message
                    showNotification('Top up berhasil ditolak!', 'warning');
                } else {
                    alert('Gagal menolak top up: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menolak top up');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = 'Ya, Tolak';
            });
        }

        // Export Functions
        function exportTopups() {
            showLoadingModal();
            window.location.href = "{{ route('supervisor.topups.export') }}";
            setTimeout(hideLoadingModal, 2000);
        }

        function exportFilteredTopups() {
            showLoadingModal();
            
            const filters = {
                student_id: $('#student_id').val(),
                status: $('#status').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val()
            };
            
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key] && filters[key].trim() !== '') {
                    params.append(key, filters[key]);
                }
            });
            
            const exportUrl = "{{ route('supervisor.topups.export') }}" + 
                             (params.toString() ? '?' + params.toString() : '');
            
            window.location.href = exportUrl;
            setTimeout(hideLoadingModal, 2000);
        }

        function showLoadingModal() {
            const modal = document.getElementById('loading-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideLoadingModal() {
            const modal = document.getElementById('loading-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const colors = {
                paid: 'bg-green-100 border-green-400 text-green-700',
                warning: 'bg-yellow-100 border-yellow-400 text-yellow-700',
                error: 'bg-red-100 border-red-400 text-red-700'
            };

            const icons = {
                paid: 'fas fa-check-circle',
                warning: 'fas fa-exclamation-triangle', 
                error: 'fas fa-times-circle'
            };

            const notificationDiv = document.createElement('div');
            notificationDiv.className = `fixed top-4 right-4 z-50 ${colors[type]} px-4 py-3 rounded shadow-lg max-w-md`;
            notificationDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="${icons[type]} mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-lg hover:opacity-70">&times;</button>
                </div>
            `;
            document.body.appendChild(notificationDiv);

            setTimeout(() => {
                if (notificationDiv.parentElement) {
                    notificationDiv.remove();
                }
            }, 5000);
        }

        // Auto-hide loading modal when user comes back to page
        window.addEventListener('focus', function() {
            setTimeout(hideLoadingModal, 500);
        });

        // Keyboard shortcut for export (Ctrl + E)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportFilteredTopups();
            }
        });
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