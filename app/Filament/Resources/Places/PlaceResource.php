<?php

namespace App\Filament\Resources\Places;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Places\Pages\{CreatePlace, EditPlace, ListPlaces};
use App\Filament\Resources\Places\RelationManagers\{StoriesRelationManager, TipsRelationManager};
use App\Filament\Support\LocaleTabs;
use App\Models\{Destination, Place, PlaceCategory};
use App\Support\JsonSearch;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{KeyValue, Select, SpatieMediaLibraryFileUpload, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, SpatieMediaLibraryImageColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';
    protected static string|UnitEnum|null $navigationGroup = 'Travel content';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->columnSpanFull()->schema([
                LocaleTabs::make([
                    'name'          => ['label' => 'Name', 'required' => true],
                    'description'   => ['label' => 'Description', 'type' => 'textarea', 'rows' => 6],
                    'fee_note'      => ['label' => 'Fee note (e.g. included in Angkor pass)'],
                    'best_time'     => ['label' => 'Best time to visit'],
                    'dress_code'    => ['label' => 'Dress code', 'type' => 'textarea', 'rows' => 2],
                    'accessibility' => ['label' => 'Accessibility', 'type' => 'textarea', 'rows' => 2],
                ]),
            ]),

            Section::make('Details')->columns(2)->columnSpanFull()->schema([
                Select::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                Select::make('place_category_id')
                    ->label('Category')
                    ->relationship('category', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->helperText('Lowercase English, used in links. Example: bayon-temple'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
                Toggle::make('is_featured')->label('Featured'),
            ]),

            Section::make('Visitor info')->columns(2)->columnSpanFull()->schema([
                TextInput::make('entrance_fee')->numeric()->prefix('$')->helperText('Foreigner price in USD'),
                TextInput::make('suggested_duration_min')->numeric()->suffix('minutes'),
                KeyValue::make('opening_hours')
                    ->keyLabel('Days')
                    ->valueLabel('Hours')
                    ->addActionLabel('Add opening hours')
                    ->columnSpanFull(),
            ]),

            Section::make('Location and GPS trigger')->columns(3)->columnSpanFull()->schema([
                TextInput::make('latitude')->numeric()->required()->minValue(-90)->maxValue(90),
                TextInput::make('longitude')->numeric()->required()->minValue(-180)->maxValue(180),
                TextInput::make('radius_m')
                    ->label('Story trigger radius')
                    ->numeric()
                    ->default(100)
                    ->suffix('meters'),
            ]),

            Section::make('Photos')->columnSpanFull()->schema([
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image(),
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
            ->modifyQueryUsing(fn (Builder $query) => $query->withExists('stories'))
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('name')
                    ->searchable(query: fn (Builder $query, string $search) => JsonSearch::in($query, 'name', $search)),
                TextColumn::make('destination.name')->label('Destination'),
                TextColumn::make('category.name')->label('Category')->badge(),
                IconColumn::make('stories_exists')->boolean()->label('Story'),
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
                SelectFilter::make('destination_id')
                    ->label('Destination')
                    ->options(fn () => Destination::orderBy('id')->get()
                        ->mapWithKeys(fn ($d) => [$d->id => $d->getTranslation('name', 'en')])->all()),
                SelectFilter::make('place_category_id')
                    ->label('Category')
                    ->options(fn () => PlaceCategory::orderBy('sort_order')->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', 'en')])->all()),
                SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published']),
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

    public static function getRelations(): array
    {
        return [
            TipsRelationManager::class,
            StoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPlaces::route('/'),
            'create' => CreatePlace::route('/create'),
            'edit'   => EditPlace::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}