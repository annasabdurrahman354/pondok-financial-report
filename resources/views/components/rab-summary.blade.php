@vite('resources/css/app.css')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 justify-items-center">
    <!-- Total Pemasukan -->
    <div class="flex justify-center items-center space-x-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-success-100 dark:bg-success-900/20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Pemasukan</p>
            <p class="text-lg font-semibold text-success-600 dark:text-success-400">{{ $formatCurrency($totalPemasukan) }}</p>
        </div>
    </div>

    <!-- Total Pengeluaran -->
    <div class="flex justify-center items-center space-x-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-danger-100 dark:bg-danger-900/20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Pengeluaran</p>
            <p class="text-lg font-semibold text-danger-600 dark:text-danger-400">{{ $formatCurrency($totalPengeluaran) }}</p>
        </div>
    </div>

    <!-- Saldo -->
    <div class="flex justify-center items-center space-x-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-info-100 dark:bg-info-900/20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Saldo</p>
            <p class="text-lg font-semibold {{ $saldoColor }}">{{ $formatCurrency($saldo) }}</p>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="mt-6">
    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
        <span>Utilisasi Anggaran</span>
        <span>{{ number_format($utilizationPercent, 1) }}%</span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
        <div class="bg-info-500 dark:bg-info-600 h-2 rounded-full transition-all duration-300" style="width: {{ min(100, $utilizationPercent) }}%"></div>
    </div>
</div>

<!-- Status Indicator -->
<div class="mt-4 p-3 rounded-lg {{ $isSurplus ? 'bg-success-50 dark:bg-success-900/10' : 'bg-danger-50 dark:bg-danger-900/10' }}">
    <div class="flex items-center space-x-2">
        <div class="w-2 h-2 rounded-full {{ $isSurplus ? 'bg-success-500' : 'bg-danger-500' }}"></div>
        <span class="text-sm font-medium {{ $isSurplus ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200' }}">
            {{ $isSurplus ? 'Anggaran Surplus' : 'Anggaran Defisit' }}
        </span>
    </div>
</div>
