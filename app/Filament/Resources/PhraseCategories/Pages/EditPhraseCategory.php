<?php

namespace App\Filament\Resources\PhraseCategories\Pages;

use App\Filament\Resources\PhraseCategories\PhraseCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPhraseCategory extends EditRecord
{
    protected static string $resource = PhraseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
