<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriPengeluaranResource\Pages;
use App\Models\KategoriPengeluaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KategoriPengeluaranResource extends Resource
{
    protected static ?string $model = KategoriPengeluaran::class;
    protected static ?string $slug = 'kategori-pengeluaran';
    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Kategori Pengeluaran';
    protected static ?string $modelLabel = 'Kategori Pengeluaran';
    protected static ?string $pluralModelLabel = 'Kategori Pengeluaran';

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
                            ->unique(ignoreRecord: true),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKategoriPengeluarans::route('/'),
            'create' => Pages\CreateKategoriPengeluaran::route('/create'),
            'edit' => Pages\EditKategoriPengeluaran::route('/{record}/edit'),
        ];
    }
}

