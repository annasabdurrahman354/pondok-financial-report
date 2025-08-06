<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ProfilePage extends Page
{
    protected static ?string $slug = 'profile';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profil';
    protected static ?int $navigationSort = 99;
    protected static ?string $title = 'Profil Saya';
    protected static string $view = 'filament.pages.profile-page';

    public bool $isEditing = false;

    public ?array $data = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->data = [
            'nama' => $user->nama,
            'email' => $user->email,
            'nomor_telepon' => $user->nomor_telepon,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(auth()->user())
            ->schema([
                Infolists\Components\Section::make('Informasi Profil')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Lengkap')
                            ->icon('heroicon-m-user')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->icon('heroicon-m-phone')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Bergabung pada')
                            ->dateTime()
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir diperbarui')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Edit Profil')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->disabled()
                            ->helperText('Email tidak dapat diubah')
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->rules([
                                Rule::unique('users', 'nomor_telepon')->ignore(auth()->id())
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    public function passwordForm()
    {
        return [
            Forms\Components\Section::make('Ubah Password')
                ->schema([
                    Forms\Components\TextInput::make('current_password')
                        ->label('Password Saat Ini')
                        ->password()
                        ->required()
                        ->rule('current_password'),

                    Forms\Components\TextInput::make('password')
                        ->label('Password Baru')
                        ->password()
                        ->required()
                        ->rule(Password::default())
                        ->same('password_confirmation'),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password Baru')
                        ->password()
                        ->required(),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Profil')
                ->icon('heroicon-m-pencil-square')
                ->color('primary')
                ->visible(fn () => !$this->isEditing)
                ->action(function () {
                    $this->isEditing = true;
                }),

            Action::make('cancel')
                ->label('Batal')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->visible(fn () => $this->isEditing)
                ->action(function () {
                    $this->isEditing = false;
                    $this->data = [
                        'nama' => auth()->user()->nama,
                        'email' => auth()->user()->email,
                        'nomor_telepon' => auth()->user()->nomor_telepon,
                    ];
                    $this->passwordData = [];
                }),

            Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-m-check')
                ->color('success')
                ->visible(fn () => $this->isEditing)
                ->action('save'),

            Action::make('changePassword')
                ->label('Ubah Password')
                ->icon('heroicon-m-key')
                ->color('warning')
                ->visible(fn () => $this->isEditing)
                ->form($this->passwordForm())
                ->action(function (array $data) {
                    $this->updatePassword($data);
                }),
        ];
    }

    public function save(): void
    {
        $user = auth()->user();

        $data = $this->form($this->makeForm())->getState();

        $user->update([
            'nama' => $data['nama'],
            'nomor_telepon' => $data['nomor_telepon'],
        ]);

        $this->isEditing = false;

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }

    public function updatePassword(array $data): void
    {
        $user = auth()->user();

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title('Password saat ini tidak benar')
                ->danger()
                ->send();
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->passwordData = [];

        Notification::make()
            ->title('Password berhasil diubah')
            ->success()
            ->send();
    }

    public function getRecord(): ?Model
    {
        return auth()->user();
    }
}
