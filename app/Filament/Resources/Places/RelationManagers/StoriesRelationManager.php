<?php

namespace App\Filament\Resources\Places\RelationManagers;

use App\Filament\Support\LocaleTabs;
use Filament\Actions\{CreateAction, DeleteAction, EditAction};
use Filament\Forms\Components\{Select, SpatieMediaLibraryFileUpload, TextInput, Toggle};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, TextColumn};

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';

    protected static ?string $title = 'Audio stories';

    public function form(Schema $schema): Schema
    {
        $audioFields = collect(LocaleTabs::LOCALES)
            ->map(fn (string $label, string $lang) => SpatieMediaLibraryFileUpload::make("audio_{$lang}")
                ->label("Audio ({$label})")
                ->collection("audio_{$lang}")
                ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/ogg'])
                ->maxSize(30720) // 30 MB
                ->downloadable())
            ->values()
            ->all();

        return $schema->components([
            LocaleTabs::make([
                'title'      => ['label' => 'Title', 'required' => true],
                'transcript' => ['label' => 'Transcript', 'type' => 'textarea', 'rows' => 8],
            ]),
            Section::make('Audio narration')
                ->description('Upload one file per language. Missing languages fall back to English.')
                ->columnSpanFull()
                ->schema($audioFields),
            TextInput::make('duration_sec')->numeric()->suffix('seconds'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('autoplay_on_arrival')->label('Auto-play when the user arrives')->default(true),
            Select::make('status')
                ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                ->default('draft')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('duration_sec')->label('Seconds'),
                IconColumn::make('autoplay_on_arrival')->boolean()->label('Auto-play'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->defaultSort('sort_order')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}