<?php

namespace App\Filament\Widgets;

use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\Places\PlaceResource;
use App\Models\Place;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;

class NeedsAttention extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Needs attention')
            ->description('Places that are not published, have no audio story, or have no Khmer name.')
            ->query(fn (): Builder => Place::query()
                ->withExists(['stories as has_story' => fn ($q) => $q->where('status', 'published')])
                ->where(function (Builder $q) {
                    $q->where('status', '!=', 'published')
                        ->orWhereNull('name->km')
                        ->orWhere('name->km', '')
                        ->orWhereDoesntHave('stories', fn ($s) => $s->where('status', 'published'));
                })
                ->latest('updated_at'))
            ->columns([
                TextColumn::make('name')->label('Place'),
                TextColumn::make('destination.name')->label('Destination'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('issues')
                    ->label('To do')
                    ->badge()
                    ->color('warning')
                    ->state(function (Place $record): array {
                        $issues = [];

                        if ($record->status !== 'published') {
                            $issues[] = 'Not published';
                        }
                        if (! $record->has_story) {
                            $issues[] = 'No audio story';
                        }
                        if (blank($record->getTranslation('name', 'km', false))) {
                            $issues[] = 'No Khmer name';
                        }

                        return $issues;
                    }),
            ])
            ->recordUrl(fn (Place $record): string => PlaceResource::getUrl('edit', ['record' => $record]))
            ->defaultPaginationPageOption(5);
    }
}