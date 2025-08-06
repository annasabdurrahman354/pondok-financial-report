<?php

namespace App\Filament\Resources;

use App\Enums\Jabatan;
use App\Enums\PondokStatus;
use App\Models\Pondok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\PondokResource\Pages;
use Illuminate\Support\Facades\Hash;

class PondokResource extends Resource
{
    protected static ?string $model = Pondok::class;
    protected static ?string $slug = 'pondok';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?string $navigationLabel = 'Pondok';
    protected static ?string $pluralModelLabel = 'Pondok';
    protected static ?string $modelLabel = 'Pondok';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pondok')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(PondokStatus::class)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(15)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('provinsi')
                            ->label('Provinsi')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kota')
                            ->label('Kota')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kecamatan')
                            ->label('Kecamatan')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kelurahan')
                            ->label('Kelurahan')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kode_pos')
                            ->label('Kode Pos')
                            ->maxLength(5)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('daerah_sambung')
                            ->label('Daerah Sambung')
                            ->maxLength(100)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengurus Pondok')
                    ->schema([
                        Forms\Components\Repeater::make('pengurusPondok')
                            ->relationship('pengurusPondok')
                            ->schema([
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama Pengurus')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('nomor_telepon')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->maxLength(15)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('jabatan')
                                    ->label('Jabatan')
                                    ->required()
                                    ->options(Jabatan::class)
                                    ->columnSpan(1)
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail) {
                                                // Check if KETUA_PONDOK is being selected
                                                if ($value == Jabatan::KETUA_PONDOK->value) {
                                                    // Get the current form state
                                                    $livewire = \Livewire\Livewire::current();
                                                    $data = $livewire->form->getState();

                                                    // Count how many KETUA_PONDOK already exist
                                                    $ketuaCount = 0;
                                                    if (isset($data['pengurusPondok'])) {
                                                        foreach ($data['pengurusPondok'] as $pengurus) {
                                                            if (isset($pengurus['jabatan']) && $pengurus['jabatan'] == Jabatan::KETUA_PONDOK->value) {
                                                                $ketuaCount++;
                                                            }
                                                        }
                                                    }

                                                    if ($ketuaCount > 1) {
                                                        $fail('Hanya boleh ada satu Ketua Pondok.');
                                                    }
                                                }
                                            };
                                        },
                                    ]),
                            ])
                            ->columns(4)
                            ->addActionLabel('Tambah Pengurus')
                            ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                            ->collapsed()
                            ->deletable(fn (Forms\Get $get): bool => count($get('pengurusPondok') ?? []) > 1)
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Pengurus Pondok')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus pengurus ini?')
                            ),
                    ]),

                Forms\Components\Section::make('Admin Pondok')
                    ->schema([
                        Forms\Components\Repeater::make('users')
                            ->relationship('users')
                            ->schema([
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama Admin')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('users', 'email', ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('nomor_telepon')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->unique('users', 'nomor_telepon', ignoreRecord: true)
                                    ->maxLength(15)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Tambah Admin')
                            ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                            ->collapsed()
                            ->minItems(1) // Ensure at least one admin exists
                            ->deletable(fn (Forms\Get $get): bool => count($get('users') ?? []) > 1)
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('Hapus Admin Pondok')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus admin ini? Pastikan masih ada minimal satu admin untuk pondok ini.')
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('nomor_telepon')
                    ->label('Nomor Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('provinsi')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kota')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('daerah_sambung')
                    ->label('Daerah Sambung')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(PondokStatus::class),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pondok')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Pondok')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Alamat')
                    ->schema([
                        Infolists\Components\TextEntry::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('provinsi')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('kota')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('kecamatan')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('kelurahan')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('kode_pos')
                            ->label('Kode Pos')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('daerah_sambung')
                            ->label('Daerah Sambung')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Pengurus Pondok')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pengurusPondok')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama')
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('nomor_telepon')
                                    ->label('Telepon')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('jabatan')
                                    ->badge()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Admin Pondok')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('users')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('nomor_telepon')
                                    ->label('Telepon')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d/m/Y H:i')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime()
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diperbarui pada')
                            ->dateTime()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
            'index' => Pages\ListPondoks::route('/'),
            'create' => Pages\CreatePondok::route('/create'),
            'view' => Pages\ViewPondok::route('/{record}'),
            'edit' => Pages\EditPondok::route('/{record}/edit'),
        ];
    }
}
