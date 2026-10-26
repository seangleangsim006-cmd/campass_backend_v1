<?php

namespace App\Filament\Resources\Destinations;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Destinations\Pages\{CreateDestination, EditDestination, ListDestinations};
use App\Filament\Support\LocaleTabs;
use App\Models\Destination;
use App\Support\JsonSearch;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{Select, SpatieMediaLibraryFileUpload, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, SpatieMediaLibraryImageColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter, TrashedFilter};

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|UnitEnum|null $navigationGroup = 'Travel content';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->columnSpanFull()->schema([
                LocaleTabs::make([
                    'name'             => ['label' => 'Name', 'required' => true],
                    'tagline'          => ['label' => 'Tagline'],
                    'description'      => ['label' => 'Description', 'type' => 'textarea', 'rows' => 6],
                    'best_season'      => ['label' => 'Best season'],
                    'how_to_get_there' => ['label' => 'How to get there', 'type' => 'textarea', 'rows' => 4],
                ]),
            ]),

            Section::make('Details')->columns(2)->columnSpanFull()->schema([
                Select::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->helperText('Lowercase English, used in links. Example: siem-reap'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
                Toggle::make('is_premium')->label('Premium destination'),
                TextInput::make('version')
                    ->label('Offline pack version')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Increases automatically when a place, story, or tip changes.'),
            ]),

            Section::make('Location')->columns(2)->columnSpanFull()->schema([
                TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90),
                TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
            ]),

            Section::make('Photos')->columnSpanFull()->schema([
                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection('cover')
                    ->image(),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->collection('gallery')
                    ->image()
                    ->multiple()
                    ->reorderable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('name')
                    ->searchable(query: fn (Builder $query, string $search) => JsonSearch::in($query, 'name', $search)),
                TextColumn::make('region.name')->label('Region'),
                TextColumn::make('places_count')->counts('places')->label('Places'),
                TextColumn::make('version')->label('Pack version')->badge(),
                IconColumn::make('is_premium')->boolean()->label('Premium'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published']),
                TernaryFilter::make('is_premium')->label('Premium'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDestinations::route('/'),
            'create' => CreateDestination::route('/create'),
            'edit'   => EditDestination::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}