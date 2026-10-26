<?php

namespace App\Filament\Resources\Regions;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Regions\Pages\{CreateRegion, EditRegion, ListRegions};
use App\Filament\Support\LocaleTabs;
use App\Models\Region;
use Filament\Actions\{DeleteAction, EditAction};
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-asia-australia';
    protected static string|UnitEnum|null $navigationGroup = 'Setup';
    protected static ?int $navigationSort = 1;

    // lookups change the app's filters, so only the super admin edits them
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            LocaleTabs::make([
                'name' => ['label' => 'Name', 'required' => true],
            ]),
            TextInput::make('slug')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText('Lowercase English, used in links. Example: south-coast'),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('slug'),
                TextColumn::make('destinations_count')->counts('destinations')->label('Destinations'),
                TextColumn::make('sort_order')->label('Order'),
            ])
            ->recordActions([
                EditAction::make(),
                // the database blocks deleting a region that still has destinations
                DeleteAction::make()->hidden(fn ($record) => $record->destinations()->withTrashed()->exists()),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRegions::route('/'),
            'create' => CreateRegion::route('/create'),
            'edit'   => EditRegion::route('/{record}/edit'),
        ];
    }
}