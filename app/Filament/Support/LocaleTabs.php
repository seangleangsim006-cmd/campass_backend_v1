<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Model;

use Filament\Forms\Components\{TextInput, Textarea};
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class LocaleTabs
{
    public const LOCALES = [
        'en' => 'English',
        'km' => 'ខ្មែរ',
        'zh' => '中文',
        'ko' => '한국어',
    ];

    /**
     * One tab per language. Each tab holds the same translatable fields.
     *
     * @param array<string, array{label?: string, type?: string, rows?: int, required?: bool}> $fields
     */
    public static function make(array $fields): Tabs
    {
        $tabs = [];

        foreach (self::LOCALES as $locale => $label) {
            $components = [];

            foreach ($fields as $name => $options) {
                $field = ($options['type'] ?? 'text') === 'textarea'
                    ? Textarea::make("{$name}.{$locale}")->rows($options['rows'] ?? 4)
                    : TextInput::make("{$name}.{$locale}");

                $components[] = $field
                    ->label($options['label'] ?? ucfirst($name))
                    ->required(($options['required'] ?? false) && $locale === 'en') // only English is mandatory
                    ->afterStateHydrated(function ($component, ?Model $record) use ($name, $locale) {
                        // load this language's text from the JSON column
                        $component->state($record?->getTranslation($name, $locale, false) ?: null);
                    });
            }

            $tabs[] = Tab::make($label)->schema($components);
        }

        return Tabs::make('Translations')->tabs($tabs)->columnSpanFull();
    }
}