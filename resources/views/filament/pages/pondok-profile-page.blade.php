<x-filament-panels::page>
    <div class="space-y-6">
        @if (!$this->isEditing)
            {{-- Show Infolist when not editing --}}
            {{ $this->infolist }}
        @else
            {{-- Show Form when editing --}}
            <form wire:submit="save">
                {{ $this->form }}
            </form>
        @endif
    </div>
</x-filament-panels::page>
