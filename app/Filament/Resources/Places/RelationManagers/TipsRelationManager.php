<?php

namespace App\Filament\Resources\Places\RelationManagers;

use App\Filament\Resources\Places\PlaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TipsRelationManager extends RelationManager
{
    protected static string $relationship = 'tips';

    protected static ?string $relatedResource = PlaceResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
