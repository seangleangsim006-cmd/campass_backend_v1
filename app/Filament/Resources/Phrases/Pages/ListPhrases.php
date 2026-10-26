<?php

namespace App\Filament\Resources\Phrases\Pages;

use App\Filament\Resources\Phrases\PhraseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhrases extends ListRecords
{
    protected static string $resource = PhraseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
