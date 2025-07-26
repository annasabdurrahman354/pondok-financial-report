<?php

namespace App\Filament\Resources\PengajuanDanaResource\Pages;

use App\Filament\Resources\PengajuanDanaResource;
use App\Models\Periode;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanDanas extends ListRecords
{
    protected static string $resource = PengajuanDanaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengajuan')
                ->icon('heroicon-o-plus')
                ->visible(fn () => $this->getResource()::canCreate()),
        ];
    }

    public function getTabs(): array
    {
        if (!auth()->user()->isAdminPusat()) {
            return [];
        }

        $tabs = [];

        // Get current active periode
        $currentPeriode = Periode::getPeriodeRabAktif();

        // If no active periode, get the latest periode
        if (!$currentPeriode) {
            $currentPeriode = Periode::orderBy('id', 'desc')->first();
        }

        // Tab 1: Current/Latest Active Periode
        if ($currentPeriode) {
            $tabs['current'] = Tab::make('Periode Aktif (' . $this->formatPeriodeLabel($currentPeriode->id) . ')')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('periode_id', $currentPeriode->id));
        }

        // Get 3 previous periodes
        $previousPeriodes = $this->getPreviousPeriodes($currentPeriode ? $currentPeriode->id : null, 3);

        foreach ($previousPeriodes as $index => $periode) {
            $tabKey = 'previous_' . ($index + 1);
            $tabs[$tabKey] = Tab::make($this->formatPeriodeLabel($periode->id))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('periode_id', $periode->id));
        }

        // Tab for all other/older periodes
        $excludedPeriodeIds = collect($tabs)->map(function ($tab, $key) use ($currentPeriode, $previousPeriodes) {
            if ($key === 'current' && $currentPeriode) {
                return $currentPeriode->id;
            }

            if (str_starts_with($key, 'previous_')) {
                $index = (int) str_replace('previous_', '', $key) - 1;
                return $previousPeriodes[$index]->id ?? null;
            }

            return null;
        })->filter()->values()->toArray();

        if (!empty($excludedPeriodeIds)) {
            $tabs['others'] = Tab::make('Periode Lainnya')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('periode_id', $excludedPeriodeIds));
        }

        return $tabs;
    }

    private function getPreviousPeriodes(?string $currentPeriodeId, int $limit = 3): array
    {
        if (!$currentPeriodeId) {
            return Periode::orderBy('id', 'desc')->limit($limit)->get()->toArray();
        }

        return Periode::where('id', '<', $currentPeriodeId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function formatPeriodeLabel(string $periodeId): string
    {
        try {
            $date = Carbon::createFromFormat('Ym', $periodeId);
            return $date->format('M Y');
        } catch (\Exception $e) {
            return $periodeId;
        }
    }
}
