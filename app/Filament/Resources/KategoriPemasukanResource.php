<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriPemasukanResource\Pages;
use App\Models\KategoriPemasukan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KategoriPemasukanResource extends Resource
{
    protected static ?string $model = KategoriPemasukan::class;
    protected static ?string $slug = 'kategori-pemasukan';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Kategori Pemasukan';
    protected static ?string $modelLabel = 'Kategori Pemasukan';
    protected static ?string $pluralModelLabel = 'Kategori Pemasukan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record?->isSisaSaldo()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('is_sisa_saldo')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->isSisaSaldo() ? 'System' : 'Custom')
                    ->colors([
                        'danger' => 'System',
                        'success' => 'Custom',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_system')
                    ->label('System Categories')
                    ->query(fn (Builder $query): Builder => $query->where('id', 1))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => ! $record->isSisaSaldo()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => ! $record->isSisaSaldo()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Filter out Sisa Saldo category
                            $deletableRecords = $records->filter(fn ($record) => ! $record->isSisaSaldo());
                            $deletableRecords->each->delete();
                        }),
                ]),
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
            'index' => Pages\ListKategoriPemasukans::route('/'),
            'create' => Pages\CreateKategoriPemasukan::route('/create'),
            'edit' => Pages\EditKategoriPemasukan::route('/{record}/edit'),
        ];
    }
}
