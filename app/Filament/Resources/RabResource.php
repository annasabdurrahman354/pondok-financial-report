<?php

namespace App\Filament\Resources;

use App\Enums\LaporanStatus;
use App\Filament\Forms\Components\BadgePlaceholder;
use App\Filament\Resources\RabResource\Pages;
use App\Models\KategoriPemasukan;
use App\Models\KategoriPengeluaran;
use App\Models\Lpj;
use App\Models\Periode;
use App\Models\Pondok;
use App\Models\Rab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RabResource extends Resource
{
    protected static ?string $model = Rab::class;
    protected static ?string $slug = 'rab';
    protected static ?string $modelLabel = 'RAB';
    protected static ?string $pluralModelLabel = 'RAB';
    protected static ?string $recordTitleAttribute = 'recordTitle';
    protected static ?string $navigationLabel = 'RAB';
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

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
                            ->content(fn ($record) => $record?->pesan_revisi ?? '')
                            ->visible(fn ($record) => $record && $record->needsRevision() && $record->pesan_revisi)
                            ->extraAttributes(['class' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4'])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('periode')
                            ->label('Periode')
                            ->content(function ($record, $operation) {
                                if ($operation == 'create') {
                                    $periodeId = Periode::getPeriodeRabAktif();

                                    if ($periodeId) {
                                        $year = substr($periodeId->id, 0, 4);
                                        $month = substr($periodeId->id, 4, 2);

                                        return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                                    }
                                    return 'Tidak ada periode RAB aktif!';
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
                                        $existingRab = Rab::where('periode_id', $value)
                                            ->where('pondok_id', $pondokId)
                                            ->exists();

                                        if ($existingRab) {
                                            $fail('RAB untuk periode ini sudah ada untuk pondok yang dipilih.');
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
                                    ->disabled(fn (Get $get) => $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required()
                                    ->disabled(fn (Get $get) => $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated(),

                                Forms\Components\Textarea::make('detail')
                                    ->label('Detail')
                                    ->disabled(fn (Get $get) => $get('kategori_pemasukan_id') == 1)
                                    ->dehydrated()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->disabled(function (Get $get) {
                                        // Disable if this is "Sisa Saldo" and has previous LPJ
                                        if ($get('kategori_pemasukan_id') == 1) {
                                            $previousPeriod = Periode::getPeriodeRabBulanSebelumAktif();
                                            if ($previousPeriod) {
                                                $previousLpj = Lpj::where('pondok_id', auth()->user()->pondok_id)
                                                    ->where('periode_id', $previousPeriod->id)
                                                    ->first();

                                                return $previousLpj !== null;
                                            }
                                        }

                                        return false;
                                    })
                                    ->dehydrated()
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

                                        return $activeItem['kategori_pemasukan_id'] == 1;
                                    })
                            ),
                    ])
                    ->columnSpan(1),

                Forms\Components\Section::make('Pengeluaran')
                    ->schema([
                        Forms\Components\Repeater::make('pengeluaran')
                            ->hiddenLabel()
                            ->relationship('pengeluaran')
                            ->schema([
                                Forms\Components\Select::make('kategori_pengeluaran_id')
                                    ->label('Kategori')
                                    ->options(KategoriPengeluaran::pluck('nama', 'id'))
                                    ->required(),

                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required(),

                                Forms\Components\Textarea::make('detail')
                                    ->label('Detail')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Pengeluaran')
                            ->minItems(1)
                            ->defaultItems(1),
                    ])
                    ->columnSpan(1),

                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\ViewField::make('summary_calculation')
                            ->hiddenLabel()
                            ->view('components.rab-summary')
                            ->viewData(function (Get $get) {
                                $pemasukan = $get('pemasukan') ?? [];
                                $pengeluaran = $get('pengeluaran') ?? [];

                                // Calculate total pemasukan
                                $totalPemasukan = 0;
                                foreach ($pemasukan as $item) {
                                    if (isset($item['nominal']) && is_numeric($item['nominal'])) {
                                        $totalPemasukan += (float) $item['nominal'];
                                    }
                                }

                                // Calculate total pengeluaran
                                $totalPengeluaran = 0;
                                foreach ($pengeluaran as $item) {
                                    if (isset($item['nominal']) && is_numeric($item['nominal'])) {
                                        $totalPengeluaran += (float) $item['nominal'];
                                    }
                                }

                                // Calculate saldo
                                $saldo = $totalPemasukan - $totalPengeluaran;

                                // Format currency
                                $formatCurrency = function ($amount) {
                                    return 'Rp ' . number_format($amount, 0, ',', '.');
                                };

                                // Calculate utilization percentage
                                $utilizationPercent = $totalPemasukan > 0 ? ($totalPengeluaran / $totalPemasukan) * 100 : 0;

                                return [
                                    'totalPemasukan' => $totalPemasukan,
                                    'totalPengeluaran' => $totalPengeluaran,
                                    'saldo' => $saldo,
                                    'formatCurrency' => $formatCurrency,
                                    'utilizationPercent' => $utilizationPercent,
                                    'saldoColor' => $saldo >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
                                    'isSurplus' => $saldo >= 0,
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
                    ->formatStateUsing(fn (Rab $record) => $record->formatted_periode)
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_pemasukan')
                    ->label('Total Pemasukan')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('total_pengeluaran')
                    ->label('Total Pengeluaran')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('status')
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
                        return \App\Models\Rab::query()
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
                    ->label(fn (Rab $record) => (auth()->user()->isAdminPusat() ? 'Ubah' : $record->isDrafted()) ? 'Ubah' : 'Revisi')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn (Rab $record) =>
                        auth()->user()->isAdminPusat()
                            ? !$record->isAccepted()
                            : ($record->isDrafted() || $record->needsRevision())
                    ),
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Rab $record) => $record->isDrafted())
                    ->action(fn (Rab $record) => $record->submit()),

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
                    ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(function (array $data, Rab $record): void {
                        $record->requestRevision($data['pesan_revisi']);
                    }),

                Tables\Actions\Action::make('accept')
                    ->label('Terima')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(fn (Rab $record) => $record->accept()),

                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->button(),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRabs::route('/'),
            'create' => Pages\CreateRab::route('/create'),
            'edit' => Pages\EditRab::route('/{record}/edit'),
            'view' => Pages\ViewRab::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        if (auth()->user()->isAdminPusat()) return true;

        $activePeriod = Periode::getPeriodeRabAktif();
        if (!$activePeriod) {
            return false;
        }

        // Check if all previous RABs and LPJs are accepted
        $userPondokId = auth()->user()->pondok_id;

        // Check previous RABs
        $pendingRabs = Rab::where('pondok_id', $userPondokId)
            ->where('status', '!=', 'diterima')
            ->exists();

        // Check previous LPJs
        $pendingLpjs = Lpj::where('pondok_id', $userPondokId)
            ->where('status', '!=', 'diterima')
            ->exists();

        return !$pendingRabs && !$pendingLpjs;
    }
}
