<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Closure;

class BadgePlaceholder extends Placeholder
{
    protected array $options = [];
    protected array $colors = [];
    protected string | Closure | null $badgeColor = null;
    protected string | Closure | null $badgeSize = 'sm';

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function color(string | Closure $color): static
    {
        $this->badgeColor = $color;

        return $this;
    }

    public function size(string $size): static
    {
        $this->badgeSize = $size;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->content(function ($record, $state) {
            $value = $state ?? (is_object($record) ? data_get($record, $this->getName()) : null);

            if (!$value) {
                return new HtmlString('<span class="text-gray-500 text-sm">—</span>');
            }

            // Get label from options or use raw value
            $label = $this->options[$value] ?? $value;

            // Determine color
            $color = $this->getBadgeColor($value, $record, $state);

            // Generate badge classes based on color and size
            $badgeClasses = $this->getBadgeClasses($color, $this->badgeSize);

            return new HtmlString(
                '<span class="' . $badgeClasses . '">' .
                e($label) .
                '</span>'
            );
        });
    }

    protected function getBadgeColor(mixed $value, mixed $record, mixed $state): string
    {
        if ($this->badgeColor) {
            return $this->evaluate($this->badgeColor, [
                'record' => $record,
                'state' => $state,
                'value' => $value,
            ]);
        }

        return $this->colors[$value] ?? 'gray';
    }

    protected function getBadgeClasses(string $color, string $size): string
    {
        $baseClasses = 'inline-flex items-center rounded-md font-medium ring-1 ring-inset';

        $sizeClasses = match($size) {
            'xs' => 'px-1.5 py-0.5 text-xs',
            'sm' => 'px-2 py-1 text-xs',
            'md' => 'px-2.5 py-1.5 text-sm',
            'lg' => 'px-3 py-2 text-base',
            default => 'px-2 py-1 text-xs',
        };

        $colorClasses = match($color) {
            'gray' => 'bg-gray-100 text-gray-800 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-400/20',
            'primary' => 'bg-blue-100 text-blue-800 ring-blue-600/20 dark:bg-blue-800 dark:text-blue-300 dark:ring-blue-400/20',
            'success' => 'bg-green-100 text-green-800 ring-green-600/20 dark:bg-green-800 dark:text-green-300 dark:ring-green-400/20',
            'warning' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20 dark:bg-yellow-800 dark:text-yellow-300 dark:ring-yellow-400/20',
            'danger' => 'bg-red-100 text-red-800 ring-red-600/20 dark:bg-red-800 dark:text-red-300 dark:ring-red-400/20',
            'info' => 'bg-cyan-100 text-cyan-800 ring-cyan-600/20 dark:bg-cyan-800 dark:text-cyan-300 dark:ring-cyan-400/20',
            'purple' => 'bg-purple-100 text-purple-800 ring-purple-600/20 dark:bg-purple-800 dark:text-purple-300 dark:ring-purple-400/20',
            'pink' => 'bg-pink-100 text-pink-800 ring-pink-600/20 dark:bg-pink-800 dark:text-pink-300 dark:ring-pink-400/20',
            'indigo' => 'bg-indigo-100 text-indigo-800 ring-indigo-600/20 dark:bg-indigo-800 dark:text-indigo-300 dark:ring-indigo-400/20',
            default => 'bg-gray-100 text-gray-800 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-400/20',
        };

        return implode(' ', [$baseClasses, $sizeClasses, $colorClasses]);
    }
}
