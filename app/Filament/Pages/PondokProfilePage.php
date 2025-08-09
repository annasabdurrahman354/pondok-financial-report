<?php

namespace App\Filament\Pages;

use App\Enums\Jabatan;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PondokStatus;

class PondokProfilePage extends Page
{
    use HasPageShield;

    // Page configuration
    protected static ?string $slug = 'pondok-profile';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Pondok';
    protected static ?string $title = 'Profil Pondok';
    protected static ?int $navigationSort = 98;
    protected static string $view = 'filament.pages.pondok-profile-page';

    // State management properties
    public bool $isEditing = false;
    public ?array $data = [];

    /**
     * Runs when the component is initialized.
     */
    public function mount(): void
    {
        // Get the pondok associated with the logged-in user
        $pondok = auth()->user()->pondok;

        // Abort if user has no pondok associated
        abort_if(!$pondok, 404);

        // Fill the data array with the pondok's current attributes including relationships
        $this->data = $pondok->attributesToArray();

        // Load pengurus pondok relationship data
        $this->data['pengurusPondok'] = $pondok->pengurusPondok()
            ->select('id', 'nama', 'nomor_telepon', 'jabatan')
            ->get()
            ->toArray();
    }

    /**
     * Defines the infolist structure for viewing data.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(auth()->user()->pondok)
            ->schema([
                Infolists\Components\Section::make('Informasi Pondok')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Pondok')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->icon('heroicon-o-phone'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Alamat')
                    ->schema([
                        Infolists\Components\TextEntry::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('provinsi'),
                        Infolists\Components\TextEntry::make('kota'),
                        Infolists\Components\TextEntry::make('kecamatan'),
                        Infolists\Components\TextEntry::make('kelurahan'),
                        Infolists\Components\TextEntry::make('kode_pos')->label('Kode Pos'),
                        Infolists\Components\TextEntry::make('daerah_sambung')->label('Daerah Sambung'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Pengurus Pondok')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pengurusPondok')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama Pengurus')
                                    ->weight(FontWeight::Medium)
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('nomor_telepon')
                                    ->label('Nomor Telepon')
                                    ->url(fn (?string $state): ?string => $state ? "tel:{$state}" : null)
                                    ->icon('heroicon-o-phone')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('jabatan')
                                    ->label('Jabatan')
                                    ->badge()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->contained(false)
                    ]),
            ]);
    }

    /**
     * Defines the form structure for editing data.
     */
    public function form(Form $form): Form
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
                            ->options(PondokStatus::class),
                        Forms\Components\TextInput::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(15),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('provinsi')->maxLength(100),
                        Forms\Components\TextInput::make('kota')->maxLength(100),
                        Forms\Components\TextInput::make('kecamatan')->maxLength(100),
                        Forms\Components\TextInput::make('kelurahan')->maxLength(100),
                        Forms\Components\TextInput::make('kode_pos')->label('Kode Pos')->maxLength(5),
                        Forms\Components\TextInput::make('daerah_sambung')->label('Daerah Sambung')->maxLength(100),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengurus Pondok')
                    ->schema([
                        Forms\Components\Repeater::make('pengurusPondok')
                            ->hiddenLabel()
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
            ])
            ->statePath('data')
            ->model(auth()->user()->pondok);
    }

    /**
     * Handles the save action.
     */
    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $pondok = auth()->user()->pondok;

            // Separate relationship data from main model data
            $pengurusPondokData = $data['pengurusPondok'] ?? [];
            unset($data['pengurusPondok']);

            // Update the main pondok data (excluding relationships)
            $pondok->update($data);

            // Handle pengurus pondok relationship manually
            if (!empty($pengurusPondokData)) {
                // Get existing pengurus IDs
                $existingIds = collect($pengurusPondokData)
                    ->filter(fn($item) => isset($item['id']) && !empty($item['id']))
                    ->pluck('id')
                    ->toArray();

                // Delete pengurus that are not in the current form data
                $pondok->pengurusPondok()
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                // Update or create pengurus
                foreach ($pengurusPondokData as $pengurusData) {
                    if (isset($pengurusData['id']) && !empty($pengurusData['id'])) {
                        // Update existing
                        $pondok->pengurusPondok()
                            ->where('id', $pengurusData['id'])
                            ->update(collect($pengurusData)->except(['id'])->toArray());
                    } else {
                        // Create new
                        $pondok->pengurusPondok()->create($pengurusData);
                    }
                }
            } else {
                // If no pengurus data, delete all existing
                $pondok->pengurusPondok()->delete();
            }

            $this->isEditing = false; // Switch back to infolist view

            Notification::make()
                ->title('Profil pondok berhasil diperbarui')
                ->success()
                ->send();

            // Refresh the data after saving
            $this->data = $pondok->fresh()->load('pengurusPondok')->attributesToArray();

        } catch (\Exception $e) {
            \Log::error('Error saving pondok profile: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $data ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Terjadi kesalahan saat menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getRecord(): ?Model
    {
        return auth()->user()->pondok;
    }

    /**
     * Defines the header actions (Edit, Save, Cancel).
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit Profil')
                ->icon('heroicon-m-pencil-square')
                ->color('primary')
                ->visible(fn () => !$this->isEditing) // Show only when not editing
                ->action(fn () => $this->isEditing = true),

            Action::make('cancel')
                ->label('Batal')
                ->icon('heroicon-m-x-mark')
                ->color('gray')
                ->visible(fn (): bool => $this->isEditing) // Show only when editing
                ->action(function (): void {
                    $this->isEditing = false;
                    $pondok = auth()->user()->pondok;
                    $this->data = $pondok->attributesToArray();
                    // Reload pengurus pondok relationship data
                    $this->data['pengurusPondok'] = $pondok->pengurusPondok()
                        ->select('id', 'nama', 'nomor_telepon', 'jabatan')
                        ->get()
                        ->toArray();
                    $this->form->fill($this->data);
                }),

            Action::make('save')
                ->label('Simpan Perubahan')
                ->icon('heroicon-m-check')
                ->color('success')
                ->visible(fn (): bool => $this->isEditing) // Show only when editing
                ->action('save'),
        ];
    }
}
