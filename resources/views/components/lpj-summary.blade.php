@vite('resources/css/app.css')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Pemasukan Card -->
    <div class="bg-success-50 dark:bg-success-900/10 rounded-lg p-5 border border-success-200 dark:border-success-800">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <h4 class="text-lg font-semibold text-success-800 dark:text-success-200">Pemasukan</h4>
            </div>
            <span class="text-xs bg-success-100 dark:bg-success-900/20 text-success-700 dark:text-success-300 px-2 py-1 rounded-full">
                {{ number_format($percentPemasukan, 1) }}%
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Rencana:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $formatCurrency($totalPemasukanRencana) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Realisasi:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $formatCurrency($totalPemasukanRealisasi) }}</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-success-200 dark:border-success-800">
                <span class="text-gray-600 dark:text-gray-400">Selisih:</span>
                <span class="font-semibold">Rp {!! $formatVariance($variancePemasukan, 'pemasukan') !!}</span>
            </div>
        </div>
    </div>

    <!-- Pengeluaran Card -->
    <div class="bg-danger-50 dark:bg-danger-900/10 rounded-lg p-5 border border-danger-200 dark:border-danger-800">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
                <h4 class="text-lg font-semibold text-danger-800 dark:text-danger-200">Pengeluaran</h4>
            </div>
            <span class="text-xs bg-danger-100 dark:bg-danger-900/20 text-danger-700 dark:text-danger-300 px-2 py-1 rounded-full">
                {{ number_format($percentPengeluaran, 1) }}%
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Rencana:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $formatCurrency($totalPengeluaranRencana) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Realisasi:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $formatCurrency($totalPengeluaranRealisasi) }}</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-danger-200 dark:border-danger-800">
                <span class="text-gray-600 dark:text-gray-400">Selisih:</span>
                <span class="font-semibold">Rp {!! $formatVariance($variancePengeluaran, 'pengeluaran') !!}</span>
            </div>
        </div>
    </div>

    <!-- Saldo Card -->
    <div class="bg-info-50 dark:bg-info-900/10 rounded-lg p-5 border border-info-200 dark:border-info-800 md:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <h4 class="text-lg font-semibold text-info-800 dark:text-info-200">Saldo</h4>
            </div>
            <span class="text-xs bg-info-100 dark:bg-info-900/20 text-info-700 dark:text-info-300 px-2 py-1 rounded-full">
                {{ $saldoRealisasi >= 0 ? 'Surplus' : 'Defisit' }}
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Rencana:</span>
                <span class="font-medium {{ $saldoRencanaColor }}">{{ $formatCurrency($saldoRencana) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Realisasi:</span>
                <span class="font-medium {{ $saldoRealisasiColor }}">{{ $formatCurrency($saldoRealisasi) }}</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-info-200 dark:border-info-800">
                <span class="text-gray-600 dark:text-gray-400">Selisih:</span>
                <span class="font-semibold">Rp {!! $formatVariance($varianceSaldo, 'saldo') !!}</span>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bars -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Pemasukan Progress -->
    <div class="space-y-2">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <span>Realisasi Pemasukan</span>
            <span>{{ number_format($percentPemasukan, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="bg-success-500 dark:bg-success-600 h-2 rounded-full transition-all duration-300" style="width: {{ min(100, $percentPemasukan) }}%"></div>
        </div>
        @if(isset($pemasukanPerformance) && $pemasukanPerformance)
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Status: {{ $pemasukanPerformance }}
            </div>
        @endif
    </div>

    <!-- Pengeluaran Progress -->
    <div class="space-y-2">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <span>Realisasi Pengeluaran</span>
            <span>{{ number_format($percentPengeluaran, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <!-- Dynamic color based on performance -->
            <div class="h-2 rounded-full transition-all duration-300 {{ $percentPengeluaran <= 100 ? 'bg-success-500 dark:bg-success-600' : 'bg-danger-500 dark:bg-danger-600' }}" style="width: {{ min(100, $percentPengeluaran) }}%"></div>
        </div>
        @if(isset($pengeluaranPerformance) && $pengeluaranPerformance)
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Status: {{ $pengeluaranPerformance }}
            </div>
        @endif
    </div>
</div>

<!-- Status Summary -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Rencana Status -->
    <div class="p-4 rounded-lg {{ $saldoRencana >= 0 ? 'bg-success-50 dark:bg-success-900/10' : 'bg-danger-50 dark:bg-danger-900/10' }}">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full {{ $saldoRencana >= 0 ? 'bg-success-500' : 'bg-danger-500' }}"></div>
            <span class="text-sm font-medium {{ $saldoRencana >= 0 ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200' }}">
                Status Rencana: {{ $statusRencana }}
            </span>
        </div>
    </div>

    <!-- Realisasi Status -->
    <div class="p-4 rounded-lg {{ $saldoRealisasi >= 0 ? 'bg-success-50 dark:bg-success-900/10' : 'bg-danger-50 dark:bg-danger-900/10' }}">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full {{ $saldoRealisasi >= 0 ? 'bg-success-500' : 'bg-danger-500' }}"></div>
            <span class="text-sm font-medium {{ $saldoRealisasi >= 0 ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200' }}">
                Status Realisasi: {{ $statusRealisasi }}
            </span>
        </div>
    </div>
</div>
