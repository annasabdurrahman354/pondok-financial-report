<?php

namespace App\Filament\Resources\PeriodeResource\RelationManagers;

use App\Models\Pondok;
use App\Models\Rab;
use App\Models\Lpj;
use App\Enums\LaporanStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RabLpjRelationManager extends RelationManager
{
    protected static string $relationship = 'rabs'; // We'll override the query anyway
    protected static ?string $title = 'RAB & LPJ Detail';
    protected static ?string $modelLabel = 'Pondok Report';
    protected static ?string $pluralModelLabel = 'Pondok Reports';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // We won't allow editing through this manager
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pondok')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rab_status')
                    ->label('Status RAB')
                    ->badge()
                    ->color(LaporanStatus::class),

                Tables\Columns\TextColumn::make('rab_total_pemasukan')
                    ->label('RAB Pemasukan')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('rab_total_pengeluaran')
                    ->label('RAB Pengeluaran')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('rab_saldo')
                    ->label('RAB Saldo')
                    ->money('IDR')
                    ->placeholder('-')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('lpj_status')
                    ->label('Status LPJ')
                    ->badge()
                    ->color(LaporanStatus::class),

                Tables\Columns\TextColumn::make('lpj_total_pemasukan_realisasi')
                    ->label('LPJ Pemasukan')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('lpj_total_pengeluaran_realisasi')
                    ->label('LPJ Pengeluaran')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('lpj_saldo_realisasi')
                    ->label('LPJ Saldo')
                    ->money('IDR')
                    ->placeholder('-')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('rab_accepted_at')
                    ->label('RAB Diterima')
                    ->dateTime()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('lpj_accepted_at')
                    ->label('LPJ Diterima')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rab_status')
                    ->label('Filter RAB Status')
                    ->options(LaporanStatus::class),

                Tables\Filters\SelectFilter::make('lpj_status')
                    ->label('Filter LPJ Status')
                    ->options(LaporanStatus::class),
            ])
            ->headerActions([
                // No create action needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view_rab')
                    ->label('Lihat RAB')
                    ->icon('heroicon-o-document-text')
                    //->url(fn ($record) => $record->rab_id ? route('filament.admin.resources.rabs.view', $record->rab_id) : null)
                    ->visible(fn ($record) => $record->rab_id !== null),

                Tables\Actions\ViewAction::make('view_lpj')
                    ->label('Lihat LPJ')
                    ->icon('heroicon-o-clipboard-document-check')
                    //->url(fn ($record) => $record->lpj_id ? route('filament.admin.resources.lpjs.view', $record->lpj_id) : null)
                    ->visible(fn ($record) => $record->lpj_id !== null),
            ])
            ->bulkActions([
                // No bulk actions needed
            ])
            ->defaultSort('nama');
    }

    protected function getTableQuery(): Builder
    {
        $periode = $this->getOwnerRecord();

        // Get all pondoks with their RAB and LPJ data for this period
        return Pondok::query()
            ->select([
                'pondok.*',
                'rab.id as rab_id',
                'rab.status as rab_status',
                'rab.accepted_at as rab_accepted_at',
                'rab.pesan_revisi as rab_pesan_revisi',
                'lpj.id as lpj_id',
                'lpj.status as lpj_status',
                'lpj.accepted_at as lpj_accepted_at',
                'lpj.pesan_revisi as lpj_pesan_revisi',

                // RAB totals
                DB::raw('(SELECT COALESCE(SUM(nominal), 0) FROM rab_pemasukan WHERE rab_pemasukan.rab_id = rab.id) as rab_total_pemasukan'),
                DB::raw('(SELECT COALESCE(SUM(nominal), 0) FROM rab_pengeluaran WHERE rab_pengeluaran.rab_id = rab.id) as rab_total_pengeluaran'),
                DB::raw('(SELECT COALESCE(SUM(nominal), 0) FROM rab_pemasukan WHERE rab_pemasukan.rab_id = rab.id) - (SELECT COALESCE(SUM(nominal), 0) FROM rab_pengeluaran WHERE rab_pengeluaran.rab_id = rab.id) as rab_saldo'),

                // LPJ totals
                DB::raw('(SELECT COALESCE(SUM(nominal_realisasi), 0) FROM lpj_pemasukan WHERE lpj_pemasukan.lpj_id = lpj.id) as lpj_total_pemasukan_realisasi'),
                DB::raw('(SELECT COALESCE(SUM(nominal_realisasi), 0) FROM lpj_pengeluaran WHERE lpj_pengeluaran.lpj_id = lpj.id) as lpj_total_pengeluaran_realisasi'),
                DB::raw('(SELECT COALESCE(SUM(nominal_realisasi), 0) FROM lpj_pemasukan WHERE lpj_pemasukan.lpj_id = lpj.id) - (SELECT COALESCE(SUM(nominal_realisasi), 0) FROM lpj_pengeluaran WHERE lpj_pengeluaran.lpj_id = lpj.id) as lpj_saldo_realisasi'),
            ])
            ->leftJoin('rab', function ($join) use ($periode) {
                $join->on('rab.pondok_id', '=', 'pondok.id')
                    ->where('rab.periode_id', '=', $periode->id);
            })
            ->leftJoin('lpj', function ($join) use ($periode) {
                $join->on('lpj.pondok_id', '=', 'pondok.id')
                    ->where('lpj.periode_id', '=', $periode->id);
            });
    }

    public function getTableRecords(): Collection
    {
        $records = $this->getTableQuery()->get();

        // Transform the records to handle null statuses
        return $records->map(function ($record) {
            $record->rab_status = $record->rab_status ? $record->rab_status : 'Belum Mengisi';
            $record->lpj_status = $record->lpj_status ? $record->lpj_status : 'Belum Mengisi';

            return $record;
        });
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
