<?php

namespace App\Filament\Resources\EmergencyContacts;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\EmergencyContacts\Pages\{CreateEmergencyContact, EditEmergencyContact, ListEmergencyContacts};
use App\Filament\Support\LocaleTabs;
use App\Models\EmergencyContact;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{Select, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

class EmergencyContactResource extends Resource
{
    protected static ?string $model = EmergencyContact::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone';
    protected static string|UnitEnum|null $navigationGroup = 'Travel info';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('destination_id')
                ->label('Destination')
                ->relationship('destination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                ->preload()
                ->placeholder('Nationwide')
                ->helperText('Leave empty for a number that works everywhere in Cambodia.'),
            Select::make('type')
                ->options([
                    'tourist_police' => 'Tourist police',
                    'police'         => 'Police',
                    'ambulance'      => 'Ambulance',
                    'hospital'       => 'Hospital',
                    'embassy'        => 'Embassy',
                    'other'          => 'Other',
                ])
                ->required(),
            TextInput::make('phone')->tel()->required()->maxLength(40),
            LocaleTabs::make([
                'name'    => ['label' => 'Name', 'required' => true],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'rows' => 2],
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->badge(),
                TextColumn::make('name'),
                TextColumn::make('phone')->copyable(),
                TextColumn::make('destination.name')->label('Destination')->placeholder('Nationwide'),
            ])
            ->filters([TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEmergencyContacts::route('/'),
            'create' => CreateEmergencyContact::route('/create'),
            'edit'   => EditEmergencyContact::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}