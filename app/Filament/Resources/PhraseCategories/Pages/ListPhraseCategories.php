<?php

namespace App\Filament\Resources\PhraseCategories\Pages;

use App\Filament\Resources\PhraseCategories\PhraseCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhraseCategories extends ListRecords
{
    protected static string $resource = PhraseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
