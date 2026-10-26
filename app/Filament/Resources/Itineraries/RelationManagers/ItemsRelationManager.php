<?php

namespace App\Filament\Resources\Itineraries\RelationManagers;

use App\Filament\Support\LocaleTabs;
use App\Models\Place;
use Filament\Actions\{CreateAction, DeleteAction, EditAction};
use Filament\Forms\Components\{Select, TextInput};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itinerary stops';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('place_id')
                ->label('Place')
                ->options(fn () => Place::orderBy('id')->get()
                    ->mapWithKeys(fn ($place) => [$place->id => $place->getTranslation('name', 'en')])
                    ->all())
                ->searchable()
                ->required(),
            TextInput::make('day_number')->label('Day')->numeric()->minValue(1)->default(1)->required(),
            TextInput::make('sort_order')->label('Order in the day')->numeric()->default(0),
            TextInput::make('time_suggestion')->maxLength(20)->helperText('Example: 05:00 or Morning'),
            LocaleTabs::make([
                'note' => ['label' => 'Note', 'type' => 'textarea', 'rows' => 2],
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('day_number')->label('Day'),
                TextColumn::make('sort_order')->label('Order'),
                TextColumn::make('place.name')->label('Place'),
                TextColumn::make('time_suggestion')->label('Time'),
                TextColumn::make('note')->limit(60),
            ])
            ->defaultSort('day_number')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}