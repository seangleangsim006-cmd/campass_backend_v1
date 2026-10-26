<?php

namespace App\Filament\Resources\Itineraries;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Itineraries\Pages\{CreateItinerary, EditItinerary, ListItineraries};
use App\Filament\Resources\Itineraries\RelationManagers\ItemsRelationManager;
use App\Filament\Support\LocaleTabs;
use App\Models\Itinerary;
use App\Support\JsonSearch;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{Select, SpatieMediaLibraryFileUpload, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};

class ItineraryResource extends Resource
{
    protected static ?string $model = Itinerary::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|UnitEnum|null $navigationGroup = 'Travel content';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->columnSpanFull()->schema([
                LocaleTabs::make([
                    'title'       => ['label' => 'Title', 'required' => true],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rows' => 5],
                ]),
            ]),

            Section::make('Details')->columns(2)->columnSpanFull()->schema([
                Select::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->helperText('Lowercase English. Example: 3-days-in-siem-reap'),
                TextInput::make('days_count')->label('Number of days')->numeric()->required()->minValue(1)->maxValue(60),
                TextInput::make('budget_estimate')->numeric()->prefix('$')->helperText('Estimated total in USD'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
                Toggle::make('is_premium')->label('Premium itinerary'),
            ]),

            Section::make('Photo')->columnSpanFull()->schema([
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(query: fn (Builder $query, string $search) => JsonSearch::in($query, 'title', $search)),
                TextColumn::make('destination.name')->label('Destination'),
                TextColumn::make('days_count')->label('Days'),
                TextColumn::make('items_count')->counts('items')->label('Stops'),
                IconColumn::make('is_premium')->boolean()->label('Premium'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published']),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ])
            ->defaultSort('id');
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListItineraries::route('/'),
            'create' => CreateItinerary::route('/create'),
            'edit'   => EditItinerary::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}