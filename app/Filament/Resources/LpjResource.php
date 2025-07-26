<?php

namespace App\Filament\Resources;

use App\Enums\LaporanStatus;
use App\Filament\Forms\Components\BadgePlaceholder;
use App\Filament\Resources\LpjResource\Pages;
use App\Models\KategoriPemasukan;
use App\Models\KategoriPengeluaran;
use App\Models\Lpj;
use App\Models\Periode;
use App\Models\Pondok;
use App\Models\Rab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LpjResource extends Resource
{
    protected static ?string $model = Lpj::class;
    protected static ?string $slug = 'lpj';
    protected static ?string $modelLabel = 'LPJ';
    protected static ?string $pluralModelLabel = 'LPJ';
    protected static ?string $recordTitleAttribute = 'recordTitle';
    protected static ?string $navigationLabel = 'LPJ';
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->isAdminPondok(), function ($q) {
                return $q->where('pondok_id', auth()->user()->pondok_id);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('pesan_revisi_content')
                            ->label('Pesan Revisi dari Admin')
                            ->content(fn ($record) => $record?->pesan_revisi ?? '-')
                            ->visible(fn ($record) => $record && $record->needsRevision() && $record->pesan_revisi)
                            ->extraAttributes(['class' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4'])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('periode')
                            ->label('Periode')
                            ->content(function ($record, $operation) {
                                if ($operation == 'create') {
                                    $periodeId = Periode::getPeriodeLpjAktif();

                                    if ($periodeId) {
                                        $year = substr($periodeId->id, 0, 4);
                                        $month = substr($periodeId->id, 4, 2);

                                        return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                                    }
                                    return 'Tidak ada periode LPJ aktif!';
                                }
                                else {
                                    $year = substr($record->periode_id, 0, 4);
                                    $month = substr($record->periode_id, 4, 2);

                                    return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                                }
                            })
                            ->hidden(fn ($operation) => auth()->user()->isAdminPusat() && $operation === 'create')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('periode_id')
                            ->label('Periode')
                            ->mask('999999')
                            ->placeholder('YYYYMM')
                            ->helperText('Masukkan tahun dan nomor bulan (cth: 202501 untuk Januari 2025)')
                            ->length(6)
                            ->required()
                            ->live(onBlur: true)
                            ->rule('digits:6')
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (empty($value) || strlen($value) !== 6) {
                                        return;
                                    }

                                    // Extract year and month
                                    $year = substr($value, 0, 4);
                                    $month = substr($value, 4, 2);

                                    // Validate year (reasonable range)
                                    if ($year < 2015 || $year > 2099) {
                                        $fail('Tahun harus diantara 2015 dan 2099.');
                                        return;
                                    }

                                    // Validate month
                                    if ($month < 1 || $month > 12) {
                                        $fail('Bulan harus diantara 01 dan 12.');
                                        return;
                                    }

                                    // Check if periode exists in the database
                                    if (!Periode::where('id', $value)->exists()) {
                                        $fail('Periode yang anda masukkan belum tercatat di sistem.');
                                    }

                                    // Check uniqueness: periode_id + pondok_id combination
                                    $pondokId = auth()->user()->isAdminPusat() ? $get('pondok_id') : auth()->user()->pondok_id;

                                    if ($pondokId) {
                                        $existingLpj = Lpj::where('periode_id', $value)
                                            ->where('pondok_id', $pondokId)
                                            ->exists();

                                        if ($existingLpj) {
                                            $fail('LPJ untuk periode ini sudah ada untuk pondok yang dipilih.');
                                        }
                                    }
                                };
                            })
                            ->afterStateUpdated(function ($livewire, $component) {
                                $livewire->validateOnly($component->getStatePath());
                            })
                            ->visible(fn ($operation) => auth()->user()->isAdminPusat() && $operation === 'create')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('pondok_id')
                            ->label('Pondok')
                            ->options(Pondok::pluck('nama', 'id'))
                            ->searchable()
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->required(fn () => auth()->user()->isAdminPusat())
                            ->live()
                            ->afterStateUpdated(function ($livewire, $component) {
                                $livewire->validateOnly('data.periode_id');
                            })
                            ->visible(fn () => auth()->user()->isAdminPusat())
                            ->columnSpanFull(),

                        BadgePlaceholder::make('status')
                            ->hiddenLabel()
                            ->options([
                                'draft' => 'Draft',
                                'diajukan' => 'Diajukan',
                                'diterima' => 'Diterima',
                                'revisi' => 'Revisi',
                            ])
                            ->colors([
                                'draft' => 'gray',
                                'diajukan' => 'warning',
                                'diterima' => 'success',
                                'revisi' => 'danger',
                            ])
                            ->visible(fn ($operation) => $operation === 'view'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(LaporanStatus::class)
                            ->live()
                            ->required(fn () => auth()->user()->isAdminPusat())
                            ->hidden(fn ($operation) => auth()->user()->isAdminPondok() || $operation === 'view')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pesan_revisi')
                            ->label('Pesan Revisi')
                            ->required(fn (Get $get) => $get('status') === 'revisi')
                            ->visible(fn (Get $get) => $get('status') === 'revisi')
                            ->required(fn (Get $get) => $get('status') === 'revisi')
                            ->hidden(fn ($operation) => auth()->user()->isAdminPondok() || $operation === 'view')
                            ->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Pemasukan')
                    ->schema([
                        Forms\Components\Repeater::make('pemasukan')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                Forms\Components\Hidden::make('rab_pemasukan_id'),

                                Forms\Components\Select::make('kategori_pemasukan_id')
                                    ->label('Kategori')
                                    ->options(KategoriPemasukan::pluck('nama', 'id'))
                                    ->disableOptionWhen(function (string $value, Get $get, string $operation) {
                                        // Only disable kategori_pemasukan_id with value '1' if already selected
                                        if ($value !== '1') {
                                            return false; // Don't disable other categories
                                        }

                                        // Get all pemasukan items
                                        $pemasukanItems = $get('../../pemasukan') ?? [];

                                        // Count how many times kategori_pemasukan_id = 1 is selected
                                        $count = 0;
                                        foreach ($pemasukanItems as $item) {
                                            if (isset($item['kategori_pemasukan_id']) && $item['kategori_pemasukan_id'] == 1) {
                                                $count++;
                                            }
                                        }

                                        // Disable if already selected once (only allow one selection of kategori 1)
                                        return $count >= 1;
                                    })
                                    ->required()
                                    ->live()
                                    ->disabled(fn ($get) => !empty($get('rab_pemasukan_id')) || $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required()
                                    ->disabled(fn ($get) => !empty($get('rab_pemasukan_id')) || $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('detail')
                                    ->label('Detail')
                                    ->disabled(fn ($get) => !empty($get('rab_pemasukan_id')) || $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('nominal_rencana')
                                    ->label('Nominal Rencana')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->disabled(fn ($get) => !empty($get('rab_pemasukan_id')))
                                    ->dehydrated()
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('nominal_realisasi')
                                    ->label('Nominal Realisasi')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true),

                                Forms\Components\Textarea::make('keterangan_realisasi')
                                    ->label('Keterangan Realisasi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Pemasukan')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->hidden(function (array $arguments, Forms\Components\Repeater $component) {
                                        $items = $component->getState();
                                        $activeItem = $items[$arguments['item']];

                                        return ! empty($activeItem['rab_pemasukan_id']);
                                    })
                            ),
                    ])
                    ->columnSpan(1),

                Forms\Components\Section::make('Pengeluaran')
                    ->schema([
                        Forms\Components\Repeater::make('pengeluaran')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                Forms\Components\Hidden::make('rab_pengeluaran_id'),

                                Forms\Components\Select::make('kategori_pengeluaran_id')
                                    ->label('Kategori')
                                    ->options(KategoriPengeluaran::pluck('nama', 'id'))
                                    ->required()
                                    ->disabled(fn ($get) => !empty($get('rab_pengeluaran_id')))
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required()
                                    ->disabled(fn ($get) => !empty($get('rab_pengeluaran_id')))
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('detail')
                                    ->label('Detail')
                                    ->disabled(fn ($get) => !empty($get('rab_pengeluaran_id')))
                                    ->dehydrated()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('nominal_rencana')
                                    ->label('Nominal Rencana')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->disabled(fn ($get) => ! empty($get('rab_pengeluaran_id')))
                                    ->dehydrated()
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('nominal_realisasi')
                                    ->label('Nominal Realisasi')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true),

                                Forms\Components\Textarea::make('keterangan_realisasi')
                                    ->label('Keterangan Realisasi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Pengeluaran')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->hidden(function (array $arguments, Forms\Components\Repeater $component) {
                                        $items = $component->getState();
                                        $activeItem = $items[$arguments['item']];

                                        return ! empty($activeItem['rab_pengeluaran_id']);
                                    })
                            ),
                    ])
                    ->columnSpan(1),

                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\ViewField::make('summary_calculation')
                            ->hiddenLabel()
                            ->view('components.lpj-summary')
                            ->viewData(function (Get $get) {
                                $pemasukan = $get('pemasukan') ?? [];
                                $pengeluaran = $get('pengeluaran') ?? [];

                                // Calculate total pemasukan rencana
                                $totalPemasukanRencana = 0;
                                foreach ($pemasukan as $item) {
                                    if (isset($item['nominal_rencana']) && is_numeric($item['nominal_rencana'])) {
                                        $totalPemasukanRencana += (float) $item['nominal_rencana'];
                                    }
                                }

                                // Calculate total pemasukan realisasi
                                $totalPemasukanRealisasi = 0;
                                foreach ($pemasukan as $item) {
                                    if (isset($item['nominal_realisasi']) && is_numeric($item['nominal_realisasi'])) {
                                        $totalPemasukanRealisasi += (float) $item['nominal_realisasi'];
                                    }
                                }

                                // Calculate total pengeluaran rencana
                                $totalPengeluaranRencana = 0;
                                foreach ($pengeluaran as $item) {
                                    if (isset($item['nominal_rencana']) && is_numeric($item['nominal_rencana'])) {
                                        $totalPengeluaranRencana += (float) $item['nominal_rencana'];
                                    }
                                }

                                // Calculate total pengeluaran realisasi
                                $totalPengeluaranRealisasi = 0;
                                foreach ($pengeluaran as $item) {
                                    if (isset($item['nominal_realisasi']) && is_numeric($item['nominal_realisasi'])) {
                                        $totalPengeluaranRealisasi += (float) $item['nominal_realisasi'];
                                    }
                                }

                                // Calculate saldo
                                $saldoRencana = $totalPemasukanRencana - $totalPengeluaranRencana;
                                $saldoRealisasi = $totalPemasukanRealisasi - $totalPengeluaranRealisasi;

                                // Calculate variance
                                $variancePemasukan = $totalPemasukanRealisasi - $totalPemasukanRencana;
                                $variancePengeluaran = $totalPengeluaranRealisasi - $totalPengeluaranRencana;
                                $varianceSaldo = $saldoRealisasi - $saldoRencana;

                                // Calculate percentages
                                $percentPemasukan = $totalPemasukanRencana > 0 ? ($totalPemasukanRealisasi / $totalPemasukanRencana) * 100 : 0;
                                $percentPengeluaran = $totalPengeluaranRencana > 0 ? ($totalPengeluaranRealisasi / $totalPengeluaranRencana) * 100 : 0;

                                // Format currency
                                $formatCurrency = function ($amount) {
                                    return 'Rp ' . number_format($amount, 0, ',', '.');
                                };

                                // Format variance with contextually appropriate colors
                                $formatVariance = function ($amount, $type = 'general') {
                                    if ($type === 'pemasukan') {
                                        // For income: positive variance (earning more than planned) is good = green
                                        // negative variance (earning less than planned) is bad = red
                                        $color = $amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                    } elseif ($type === 'pengeluaran') {
                                        // For expenses: positive variance (spending more than planned) is bad = red
                                        // negative variance (spending less than planned) is good = green
                                        $color = $amount >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
                                    } else {
                                        // For saldo: positive variance (better balance than planned) is good = green
                                        // negative variance (worse balance than planned) is bad = red
                                        $color = $amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                    }

                                    $sign = $amount >= 0 ? '+' : '';
                                    return "<span class=\"{$color}\">{$sign}" . number_format($amount, 0, ',', '.') . "</span>";
                                };

                                // Color classes for saldo based on financial health
                                $saldoRencanaColor = $saldoRencana >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                $saldoRealisasiColor = $saldoRealisasi >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';

                                // Enhanced status indicators with performance context
                                $statusRencana = $saldoRencana >= 0 ? 'Surplus' : 'Defisit';
                                $statusRealisasi = $saldoRealisasi >= 0 ? 'Surplus' : 'Defisit';

                                // Add performance indicators for better context
                                $pemasukanPerformance = '';
                                if ($totalPemasukanRencana > 0) {
                                    if ($percentPemasukan >= 100) {
                                        $pemasukanPerformance = 'Tercapai';
                                    } elseif ($percentPemasukan >= 80) {
                                        $pemasukanPerformance = 'Baik';
                                    } elseif ($percentPemasukan >= 60) {
                                        $pemasukanPerformance = 'Cukup';
                                    } else {
                                        $pemasukanPerformance = 'Kurang';
                                    }
                                }

                                $pengeluaranPerformance = '';
                                if ($totalPengeluaranRencana > 0) {
                                    if ($percentPengeluaran <= 80) {
                                        $pengeluaranPerformance = 'Terkendali';
                                    } elseif ($percentPengeluaran <= 100) {
                                        $pengeluaranPerformance = 'Sesuai';
                                    } elseif ($percentPengeluaran <= 120) {
                                        $pengeluaranPerformance = 'Melebihi';
                                    } else {
                                        $pengeluaranPerformance = 'Berlebihan';
                                    }
                                }

                                return [
                                    'totalPemasukanRencana' => $totalPemasukanRencana,
                                    'totalPemasukanRealisasi' => $totalPemasukanRealisasi,
                                    'totalPengeluaranRencana' => $totalPengeluaranRencana,
                                    'totalPengeluaranRealisasi' => $totalPengeluaranRealisasi,
                                    'saldoRencana' => $saldoRencana,
                                    'saldoRealisasi' => $saldoRealisasi,
                                    'variancePemasukan' => $variancePemasukan,
                                    'variancePengeluaran' => $variancePengeluaran,
                                    'varianceSaldo' => $varianceSaldo,
                                    'percentPemasukan' => $percentPemasukan,
                                    'percentPengeluaran' => $percentPengeluaran,
                                    'formatCurrency' => $formatCurrency,
                                    'formatVariance' => $formatVariance,
                                    'saldoRencanaColor' => $saldoRencanaColor,
                                    'saldoRealisasiColor' => $saldoRealisasiColor,
                                    'statusRencana' => $statusRencana,
                                    'statusRealisasi' => $statusRealisasi,
                                    'pemasukanPerformance' => $pemasukanPerformance,
                                    'pengeluaranPerformance' => $pengeluaranPerformance,
                                ];
                            })
                            ->live()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pondok.nama')
                    ->label('Pondok')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()->isAdminPusat()),

                Tables\Columns\TextColumn::make('periode_id')
                    ->label('Periode')
                    ->formatStateUsing(fn (Lpj $record) => $record->formatted_periode)
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_pemasukan_realisasi')
                    ->label('Total Pemasukan Realisasi')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('total_pengeluaran_realisasi')
                    ->label('Total Pengeluaran Realisasi')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('saldo_realisasi')
                    ->label('Saldo Realisasi')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('pesan_revisi')
                    ->label('Pesan Revisi')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->pesan_revisi),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->options(function () {
                        // Get unique periode_id values and format them
                        return \App\Models\Lpj::query()
                            ->distinct()
                            ->pluck('periode_id')
                            ->mapWithKeys(function ($periodeId) {
                                $year = substr($periodeId, 0, 4);
                                $month = substr($periodeId, 4, 2);
                                $monthName = \Carbon\Carbon::createFromFormat('m', $month)->format('F');
                                $formattedPeriode = $monthName . ' ' . $year;

                                return [$periodeId => $formattedPeriode];
                            })
                            ->sort()
                            ->reverse()
                            ->toArray();
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('pondok_id')
                    ->label('Pondok')
                    ->relationship('pondok', 'nama')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->isAdminPusat()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(LaporanStatus::class),
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('periode_id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make('edit')
                    ->label(fn (Lpj $record) => (auth()->user()->isAdminPusat() ? 'Ubah' : $record->isDrafted()) ? 'Ubah' : 'Revisi')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn (Lpj $record) =>
                        auth()->user()->isAdminPusat()
                            ? !$record->isAccepted()
                            : ($record->isDrafted() || $record->needsRevision())
                    ),

                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Lpj $record) => $record->isDrafted())
                    ->action(fn (Lpj $record) => $record->submit()),

                Tables\Actions\Action::make('requestRevision')
                    ->label('Minta Revisi')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-arrow-up-on-square-stack')
                    ->form([
                        Forms\Components\Textarea::make('pesan_revisi')
                            ->label('Pesan Revisi')
                            ->required()
                    ])
                    ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(function (array $data, Lpj $record): void {
                        $record->requestRevision($data['pesan_revisi']);
                    }),

                Tables\Actions\Action::make('accept')
                    ->label('Terima')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(fn (Lpj $record) => $record->accept()),

                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->button(),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
            ]);
    }

    /*
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('pesan_revisi')
                            ->label('Pesan Revisi dari Admin')
                            ->visible(fn ($record) => $record && $record->status === 'revisi' && $record->pesan_revisi)
                            ->badge()
                            ->color('danger')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('periode_display')
                            ->label('Periode')
                            ->state(function ($record) {
                                if ($record && $record->periode_id) {
                                    $year = substr($record->periode_id, 0, 4);
                                    $month = substr($record->periode_id, 4, 2);
                                    return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                                }
                                return 'Tidak ada periode';
                            })
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('pondok.nama')
                            ->label('Pondok')
                            ->visible(fn () => auth()->user()->isAdminPusat())
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Infolists\Components\Section::make('Pemasukan')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pemasukan')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('kategoriPemasukan.nama')
                                    ->label('Kategori'),

                                Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama'),

                                Infolists\Components\TextEntry::make('detail')
                                    ->label('Detail')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('nominal_rencana')
                                    ->label('Nominal Rencana')
                                    ->money('IDR')
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('nominal_realisasi')
                                    ->label('Nominal Realisasi')
                                    ->money('IDR')
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('keterangan_realisasi')
                                    ->label('Keterangan Realisasi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->columnSpan(1),

                Infolists\Components\Section::make('Pengeluaran')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pengeluaran')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('kategoriPengeluaran.nama')
                                    ->label('Kategori'),

                                Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama'),

                                Infolists\Components\TextEntry::make('detail')
                                    ->label('Detail')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('nominal_rencana')
                                    ->label('Nominal Rencana')
                                    ->money('IDR')
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('nominal_realisasi')
                                    ->label('Nominal Realisasi')
                                    ->money('IDR')
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('keterangan_realisasi')
                                    ->label('Keterangan Realisasi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->columnSpan(1),

                Infolists\Components\Section::make('Ringkasan Keuangan')
                    ->schema([
                        Infolists\Components\ViewEntry::make('summary_calculation')
                            ->hiddenLabel()
                            ->view('components.lpj-summary')
                            ->viewData(function ($record) {
                                $pemasukan = $record->pemasukan ?? collect();
                                $pengeluaran = $record->pengeluaran ?? collect();

                                // Calculate total pemasukan
                                $totalPemasukanRencana = $pemasukan->sum('nominal_rencana');
                                $totalPemasukanRealisasi = $pemasukan->sum('nominal_realisasi');

                                // Calculate total pengeluaran
                                $totalPengeluaranRencana = $pengeluaran->sum('nominal_rencana');
                                $totalPengeluaranRealisasi = $pengeluaran->sum('nominal_realisasi');

                                // Calculate saldo
                                $saldoRencana = $totalPemasukanRencana - $totalPengeluaranRencana;
                                $saldoRealisasi = $totalPemasukanRealisasi - $totalPengeluaranRealisasi;

                                // Calculate variance
                                $variancePemasukan = $totalPemasukanRealisasi - $totalPemasukanRencana;
                                $variancePengeluaran = $totalPengeluaranRealisasi - $totalPengeluaranRencana;
                                $varianceSaldo = $saldoRealisasi - $saldoRencana;

                                // Calculate percentages
                                $percentPemasukan = $totalPemasukanRencana > 0 ? ($totalPemasukanRealisasi / $totalPemasukanRencana) * 100 : 0;
                                $percentPengeluaran = $totalPengeluaranRencana > 0 ? ($totalPengeluaranRealisasi / $totalPengeluaranRencana) * 100 : 0;

                                // Format currency
                                $formatCurrency = function ($amount) {
                                    return 'Rp ' . number_format($amount, 0, ',', '.');
                                };

                                // Format variance with color
                                $formatVariance = function ($amount) {
                                    $color = $amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                    $sign = $amount >= 0 ? '+' : '';
                                    return "<span class=\"{$color}\">{$sign}" . number_format($amount, 0, ',', '.') . "</span>";
                                };

                                // Color classes for saldo
                                $saldoRencanaColor = $saldoRencana >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                $saldoRealisasiColor = $saldoRealisasi >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';

                                // Status indicators
                                $statusRencana = $saldoRencana >= 0 ? 'Surplus' : 'Defisit';
                                $statusRealisasi = $saldoRealisasi >= 0 ? 'Surplus' : 'Defisit';

                                return [
                                    'totalPemasukanRencana' => $totalPemasukanRencana,
                                    'totalPemasukanRealisasi' => $totalPemasukanRealisasi,
                                    'totalPengeluaranRencana' => $totalPengeluaranRencana,
                                    'totalPengeluaranRealisasi' => $totalPengeluaranRealisasi,
                                    'saldoRencana' => $saldoRencana,
                                    'saldoRealisasi' => $saldoRealisasi,
                                    'variancePemasukan' => $variancePemasukan,
                                    'variancePengeluaran' => $variancePengeluaran,
                                    'varianceSaldo' => $varianceSaldo,
                                    'percentPemasukan' => $percentPemasukan,
                                    'percentPengeluaran' => $percentPengeluaran,
                                    'formatCurrency' => $formatCurrency,
                                    'formatVariance' => $formatVariance,
                                    'saldoRencanaColor' => $saldoRencanaColor,
                                    'saldoRealisasiColor' => $saldoRealisasiColor,
                                    'statusRencana' => $statusRencana,
                                    'statusRealisasi' => $statusRealisasi,
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLpjs::route('/'),
            'create' => Pages\CreateLpj::route('/create'),
            'edit' => Pages\EditLpj::route('/{record}/edit'),
            'view' => Pages\ViewLpj::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        if (auth()->user()->isAdminPusat()) return true;

        $activePeriode = Periode::getPeriodeLpjAktif();
        if (!$activePeriode) {
            return false;
        }

        $userPondokId = auth()->user()->pondok_id;

        // Check if all previous RABs and LPJs are accepted
        $previousRabs = Rab::where('pondok_id', $userPondokId)
            ->where('status', '!=', 'diterima')
            ->exists();

        $previousLpjs = Lpj::where('pondok_id', $userPondokId)
            ->where('status', '!=', 'diterima')
            ->exists();

        return !$previousRabs && !$previousLpjs;
    }
}
