<?php

namespace App\Filament\Resources\Phrases\Pages;

use App\Filament\Resources\Phrases\PhraseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPhrase extends EditRecord
{
    protected static string $resource = PhraseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
