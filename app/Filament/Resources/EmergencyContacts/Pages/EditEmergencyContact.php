<?php

namespace App\Filament\Resources\EmergencyContacts\Pages;

use App\Filament\Resources\EmergencyContacts\EmergencyContactResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEmergencyContact extends EditRecord
{
    protected static string $resource = EmergencyContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
