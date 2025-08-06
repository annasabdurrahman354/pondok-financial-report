<?php

namespace App\Filament\Resources;

use App\Enums\LaporanStatus;
use App\Filament\Forms\Components\BadgePlaceholder;
use App\Filament\Resources\PengajuanDanaResource\Pages;
use App\Filament\Resources\PengajuanDanaResource\Widgets\PengajuanDanaStatsWidget;
use App\Models\PengajuanDana;
use App\Models\Pondok;
use App\Models\Periode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PengajuanDanaResource extends Resource
{
    protected static ?string $model = PengajuanDana::class;
    protected static ?string $slug = 'pengajuan-dana';
    protected static ?string $modelLabel = 'Pengajuan Dana';

    protected static ?string $pluralModelLabel = 'Pengajuan Dana';
    protected static ?string $recordTitleAttribute = 'recordTitle';
    protected static ?string $navigationLabel = 'Pengajuan Dana';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 3;

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
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\Placeholder::make('pesan_revisi_content')
                            ->label('Pesan Revisi dari Admin')
                            ->content(fn ($record) => $record?->pesan_revisi ?? '')
                            ->visible(fn ($record) => $record && $record->status === 'revisi' && $record->pesan_revisi)
                            ->extraAttributes(['class' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4'])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('periode')
                            ->label('Periode')
                            ->content(function () {
                                $periodeId = Periode::getPeriodeLpjAktif();

                                if ($periodeId) {
                                    $year = substr($periodeId->id, 0, 4);
                                    $month = substr($periodeId->id, 4, 2);

                                    return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                                }

                                return 'Tidak ada periode LPJ aktif!';
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
                                        $existingPengajuanDana = PengajuanDana::where('periode_id', $value)
                                            ->where('pondok_id', $pondokId)
                                            ->exists();

                                        if ($existingPengajuanDana) {
                                            $fail('Pengajuan dana untuk periode ini sudah ada untuk pondok yang dipilih.');
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
                            ->required(fn () => auth()->user()->isAdminPusat())
                            ->visible(fn () => auth()->user()->isAdminPusat())
                            ->disabled(fn ($operation) => $operation === 'edit')
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


                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('penjelasan')
                            ->label('Penjelasan')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('berkas')
                            ->label('Berkas Pendukung')
                            ->disk('public')
                            ->directory('pengajuan-dana')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint','application/msword'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->getUploadedFileNameForStorageUsing(
                                function (TemporaryUploadedFile $file, Get $get) {
                                    // Get periode_id
                                    $periodeId = $get('periode_id');
                                    if (!$periodeId && !auth()->user()->isAdminPusat()) {
                                        $activePeriod = Periode::getPeriodeLpjAktif();
                                        $periodeId = $activePeriod?->id;
                                    }

                                    // Get pondok name
                                    $pondokId = $get('pondok_id') ?? auth()->user()->pondok_id;
                                    $pondokNama = '';

                                    if ($pondokId) {
                                        $pondok = Pondok::find($pondokId);
                                        $pondokNama = $pondok ? str_replace([' ', '/'], '-', $pondok->nama) : 'unknown';
                                    }

                                    // Get file extension
                                    $extension = $file->getClientOriginalExtension();

                                    // Create filename: dana-periode_id-pondok_nama.extension
                                    $filename = 'Dana-' . ($periodeId ?? 'unknown') . '-' . $pondokNama . '.' . $extension;

                                    return $filename;
                                }
                            )
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pesan_revisi')
                            ->label('Pesan Revisi')
                            ->required(fn (Get $get) => $get('status') === 'revisi')
                            ->visible(fn (Get $get) => $get('status') === 'revisi')
                            ->required(fn (Get $get) => $get('status') === 'revisi')
                            ->hidden(fn ($operation) => auth()->user()->isAdminPondok() || $operation === 'view')
                            ->columnSpanFull()
                    ])
                    ->columns(2),
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
                    ->formatStateUsing(fn (PengajuanDana $record) => $record->formatted_periode)
                    ->sortable(),


                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

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
                        return \App\Models\PengajuanDana::query()
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
                    ->options(Pondok::pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->isAdminPusat()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(LaporanStatus::class),
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make('edit')
                    ->label(fn (PengajuanDana $record) => (auth()->user()->isAdminPusat() ? 'Ubah' : $record->isDrafted()) ? 'Ubah' : 'Revisi')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn (PengajuanDana $record) =>
                    auth()->user()->isAdminPusat()
                        ? !$record->isAccepted()
                        : ($record->isDrafted() || $record->needsRevision())
                    ),
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (PengajuanDana $record) => $record->isDrafted())
                    ->action(fn (PengajuanDana $record) => $record->submit()),

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
                    ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(function (array $data, PengajuanDana $record): void {
                        $record->requestRevision($data['pesan_revisi']);
                    }),

                Tables\Actions\Action::make('accept')
                    ->label('Terima')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                    ->action(fn (PengajuanDana $record) => $record->accept()),

                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->button(),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Infolists\Components\TextEntry::make('pondok.nama')
                            ->label('Pondok'),

                        Infolists\Components\TextEntry::make('periode.id')
                            ->label('Periode')
                            ->formatStateUsing(function ($state) {
                                $year = substr($state, 0, 4);
                                $month = substr($state, 4, 2);

                                return \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
                            }),

                        Infolists\Components\TextEntry::make('nominal')
                            ->label('Nominal')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        Infolists\Components\TextEntry::make('accepted_at')
                            ->label('Tanggal Diterima')
                            ->dateTime('d M Y H:i')
                            ->visible(fn (PengajuanDana $record) => $record->isAccepted()),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime('d M Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Detail')
                    ->schema([
                        Infolists\Components\TextEntry::make('penjelasan')
                            ->label('Penjelasan')
                            ->prose()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('berkas')
                            ->label('Berkas Pendukung')
                            ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Tidak ada berkas')
                            ->url(fn ($state) => $state ? Storage::disk('public')->url($state) : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('pesan_revisi')
                            ->label('Pesan Revisi')
                            ->prose()
                            ->visible(fn (PengajuanDana $record) => $record->needsRevision() && !empty($record->pesan_revisi))
                            ->extraAttributes(['class' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PengajuanDanaStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanDanas::route('/'),
            'create' => Pages\CreatePengajuanDana::route('/create'),
            'view' => Pages\ViewPengajuanDana::route('/{record}'),
            'edit' => Pages\EditPengajuanDana::route('/{record}/edit'),
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

        $pendingPengajuanDana = PengajuanDana::where('pondok_id', $userPondokId)
            ->where('status', '!=', 'diterima')
            ->exists();

        return !$pendingPengajuanDana;
    }
}
