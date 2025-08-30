<!-- resources/views/guru/saldo-recap.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-wallet mr-2"></i>Rekap Saldo Siswa
            </h2>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    Total Siswa: <span class="font-semibold">{{ $totalSiswa }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-coins text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-blue-200">Total Saldo Terkumpul</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-green-200">Total Siswa</p>
                        <p class="text-3xl font-bold">{{ $totalSiswa }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-purple-200">Rata-rata Saldo</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($rataSaldo, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter dan Search -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <input type="text" 
                               placeholder="Cari nama siswa atau NIS..." 
                               class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               id="searchInput">
                    </div>
                    <div>
                        <select class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="sortBy">
                            <option value="balance_desc">Saldo Tertinggi</option>
                            <option value="balance_asc">Saldo Terendah</option>
                            <option value="name_asc">Nama A-Z</option>
                            <option value="name_desc">Nama Z-A</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="exportToExcel()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </button>
                    <button onclick="printReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Saldo -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-table mr-2"></i>Data Saldo Siswa
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="saldoTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Siswa
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIS
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Saldo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bergabung
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $index => $student)
                            <tr class="hover:bg-gray-50 student-row" data-name="{{ strtolower($student->full_name) }}" data-nis="{{ $student->student_id }}" data-balance="{{ $student->balance }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $students->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $student->full_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->student_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($student->balance, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->balance >= 50000)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-green-400 mr-1"></i>Saldo Tinggi
                                        </span>
                                    @elseif($student->balance >= 20000)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-circle text-yellow-400 mr-1"></i>Saldo Sedang
                                        </span>
                                    @elseif($student->balance > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-circle text-orange-400 mr-1"></i>Saldo Rendah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-circle text-red-400 mr-1"></i>Saldo Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-500 font-medium">Belum ada data siswa</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($students->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                const name = row.dataset.name;
                const nis = row.dataset.nis;
                
                if (name.includes(searchTerm) || nis.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Sort functionality
        document.getElementById('sortBy').addEventListener('change', function() {
            const sortBy = this.value;
            const tbody = document.querySelector('#saldoTable tbody');
            const rows = Array.from(tbody.querySelectorAll('.student-row'));
            
            rows.sort((a, b) => {
                switch(sortBy) {
                    case 'balance_desc':
                        return parseInt(b.dataset.balance) - parseInt(a.dataset.balance);
                    case 'balance_asc':
                        return parseInt(a.dataset.balance) - parseInt(b.dataset.balance);
                    case 'name_asc':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'name_desc':
                        return b.dataset.name.localeCompare(a.dataset.name);
                    default:
                        return 0;
                }
            });
            
            rows.forEach(row => tbody.appendChild(row));
        });

        // Export to Excel function
        function exportToExcel() {
            // Implementasi export ke Excel
            alert('Fitur export Excel akan segera tersedia');
        }

        // Print function
        function printReport() {
            window.print();
        }
    </script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-title {
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 20px;
            }
        }
    </style>
</x-app-layout>