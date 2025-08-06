<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">

            <div class="flex-1">
                <h2
                    class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white"
                >
                    Export Laporan Pondok
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Jumlah Pondok {{ $totalPondok }}
                </p>
            </div>

            {{ $this->exportAction }}
        </div>
    </x-filament::section>
    <x-filament-actions::modals />
</x-filament-widgets::widget>

