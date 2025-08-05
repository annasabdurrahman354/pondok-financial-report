<?php

namespace App\Filament\Resources\PeriodeResource\RelationManagers;

use App\Enums\LaporanStatus;
use App\Filament\Resources\LpjResource;
use App\Filament\Resources\RabResource;
use App\Models\Lpj;
use App\Models\Pondok;
use App\Models\Rab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PondokLaporanRelationManager extends RelationManager
{
    protected static string $relationship = 'rabs'; // Dummy relationship

    protected static ?string $title = 'Laporan Pondok';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
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
                    ->getStateUsing(function (Pondok $record) {
                        $rab = Rab::where('pondok_id', $record->id)
                            ->where('periode_id', $this->ownerRecord->id)
                            ->first();

                        return $rab?->status;
                    })
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Belum Mengisi')
                    ->color(fn ($state) => $state?->getColor() ?? 'danger'),


                Tables\Columns\TextColumn::make('lpj_status')
                    ->label('Status LPJ')
                    ->badge()
                    ->getStateUsing(function (Pondok $record) {
                        $lpj = Lpj::where('pondok_id', $record->id)
                            ->where('periode_id', $this->ownerRecord->id)
                            ->first();

                        return $lpj?->status;
                    })
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Belum Mengisi')
                    ->color(fn ($state) => $state?->getColor() ?? 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rab_status')
                    ->label('Status RAB')
                    ->options(
                        collect(LaporanStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                            ->put('belum_mengisi', 'Belum Mengisi')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'];
                        if ($status === null) {
                            return;
                        }

                        if ($status === 'belum_mengisi') {
                            return $query->whereDoesntHave('rab', fn (Builder $q) => $q->where('periode_id', $this->ownerRecord->id));
                        }

                        return $query->whereHas('rab', fn (Builder $q) => $q->where('periode_id', $this->ownerRecord->id)->where('status', $status));
                    }),
                Tables\Filters\SelectFilter::make('lpj_status')
                    ->label('Status LPJ')
                    ->options(
                        collect(LaporanStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                            ->put('belum_mengisi', 'Belum Mengisi')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'];
                        if ($status === null) {
                            return;
                        }

                        if ($status === 'belum_mengisi') {
                            return $query->whereDoesntHave('lpj', fn (Builder $q) => $q->where('periode_id', $this->ownerRecord->id));
                        }

                        return $query->whereHas('lpj', fn (Builder $q) => $q->where('periode_id', $this->ownerRecord->id)->where('status', $status));
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_rab')
                        ->label('Lihat RAB')
                        ->icon('heroicon-o-document-currency-dollar')
                        ->url(function (Pondok $record) {
                            $rab = Rab::where('pondok_id', $record->id)
                                ->where('periode_id', $this->ownerRecord->id)
                                ->first();
                            return $rab ? RabResource::getUrl('view', ['record' => $rab]) : null;
                        })
                        ->visible(function (Pondok $record) {
                            return Rab::where('pondok_id', $record->id)
                                ->where('periode_id', $this->ownerRecord->id)
                                ->exists();
                        }),

                    Tables\Actions\Action::make('view_lpj')
                        ->label('Lihat LPJ')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->url(function (Pondok $record) {
                            $lpj = Lpj::where('pondok_id', $record->id)
                                ->where('periode_id', $this->ownerRecord->id)
                                ->first();
                            return $lpj ? LpjResource::getUrl('view', ['record' => $lpj]) : null;
                        })
                        ->visible(function (Pondok $record) {
                            return Lpj::where('pondok_id', $record->id)
                                ->where('periode_id', $this->ownerRecord->id)
                                ->exists();
                        }),
                ])
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Pondok::query();
    }

    public function isReadOnly(): bool
    {
        return true; // We don't want to edit Pondok from here
    }
}
