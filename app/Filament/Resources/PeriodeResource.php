<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodeResource\Pages;
use App\Filament\Resources\PeriodeResource\RelationManagers;
use App\Models\Periode;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PeriodeResource extends Resource
{
    protected static ?string $model = Periode::class;
    protected static ?string $slug = 'periode';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?int $navigationSort = -1;
    protected static ?string $navigationLabel = 'Periode';
    protected static ?string $modelLabel = 'Periode';
    protected static ?string $pluralModelLabel = 'Periode';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $currentYear = Carbon::now()->year;
                                $years = [];
                                for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
                                    $years[$i] = $i;
                                }

                                return $years;
                            })
                            ->required()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get, $record) {
                                // Only populate on edit (when record exists)
                                if ($record && $record->id) {
                                    $year = substr($record->id, 0, 4);
                                    $set('tahun', (int)$year);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $month = $get('bulan');
                                if ($state && $month) {
                                    $set('id', $state.str_pad($month, 2, '0', STR_PAD_LEFT));
                                }
                            }),

                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->required()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get, $record) {
                                // Only populate on edit (when record exists)
                                if ($record && $record->id) {
                                    $month = substr($record->id, 4, 2);
                                    $set('bulan', (int)$month);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $year = $get('tahun');
                                if ($year && $state) {
                                    $set('id', $year.str_pad($state, 2, '0', STR_PAD_LEFT));
                                }
                            }),
                    ]),

                Forms\Components\Hidden::make('id'),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('batas_awal_rab')
                            ->label('Mulai Pengisian RAB')
                            ->required(),

                        Forms\Components\DateTimePicker::make('batas_akhir_rab')
                            ->label('Akhir Pengisian RAB')
                            ->required()
                            ->after('batas_awal_rab'),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('batas_awal_lpj')
                            ->label('Mulai Pengisian LPJ')
                            ->required()
                            ->after('batas_akhir_rab'),

                        Forms\Components\DateTimePicker::make('batas_akhir_lpj')
                            ->label('Akhir Pengisian LPJ')
                            ->required()
                            ->after('batas_awal_lpj'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('formatted_periode')
                    ->label('Periode')
                    ->getStateUsing(function (Periode $record) {
                        $year = substr($record->id, 0, 4);
                        $month = substr($record->id, 4, 2);
                        $monthName = Carbon::createFromFormat('m', $month)->format('F');

                        return $monthName.' '.$year;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('batas_awal_rab')
                    ->label('Awal RAB')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('batas_akhir_rab')
                    ->label('Akhir RAB')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('batas_awal_lpj')
                    ->label('Awal LPJ')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('batas_akhir_lpj')
                    ->label('Akhir LPJ')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Periode $record) {
                        $today = Carbon::today();

                        if ($today >= $record->batas_awal_rab && $today <= $record->batas_akhir_rab) {
                            return 'RAB';
                        } elseif ($today >= $record->batas_awal_lpj && $today <= $record->batas_akhir_lpj) {
                            return 'LPJ';
                        } else {
                            return 'Tidak Aktif';
                        }
                    })
                    ->colors([
                        'RAB' => 'success',
                        'LPJ' => 'primary',
                        'Tidak Aktif' => 'gray',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RabLpjRelationManager::class,
            RelationManagers\PondokLaporanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodes::route('/'),
            'view' => Pages\ViewPeriode::route('/{record}'),
            'create' => Pages\CreatePeriode::route('/create'),
            'edit' => Pages\EditPeriode::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isAdminPusat();
    }
}
