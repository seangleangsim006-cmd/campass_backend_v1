<?php

namespace App\Filament\Resources\Events;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Events\Pages\{CreateEvent, EditEvent, ListEvents};
use App\Filament\Support\LocaleTabs;
use App\Models\Event;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{DatePicker, Select, SpatieMediaLibraryFileUpload, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, SpatieMediaLibraryImageColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};
class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Travel info';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->columnSpanFull()->schema([
                LocaleTabs::make([
                    'name'        => ['label' => 'Name', 'required' => true],
                    'description' => ['label' => 'What to expect, what is closed, etiquette', 'type' => 'textarea', 'rows' => 5],
                ]),
            ]),

            Section::make('When and where')->columns(2)->columnSpanFull()->schema([
                DatePicker::make('starts_on')->required(),
                DatePicker::make('ends_on')->afterOrEqual('starts_on'),
                Toggle::make('is_recurring_yearly')
                    ->label('Repeats every year')
                    ->helperText('Use the dates of one year as an example.'),
                Select::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->placeholder('Nationwide')
                    ->helperText('Leave empty for a festival celebrated across Cambodia.'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
            ]),
            Section::make('Photos')->columnSpanFull()->schema([
    SpatieMediaLibraryFileUpload::make('cover')
        ->label('Cover photo')
        ->collection('cover')
        ->image()
        ->maxSize(5120),                 // 5 MB

    SpatieMediaLibraryFileUpload::make('gallery')
        ->label('More photos')
        ->collection('gallery')
        ->image()
        ->multiple()                     // allows an array of images
        ->reorderable()                  // drag to change the order
        ->maxFiles(10)
        ->maxSize(5120)
        ->helperText('Up to 10 photos, 5 MB each. The first one is shown first in the app.'),
]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('name'),
                TextColumn::make('starts_on')->date()->label('Starts'),
                TextColumn::make('ends_on')->date()->label('Ends')->placeholder('-'),
                TextColumn::make('destination.name')->label('Where')->placeholder('Nationwide'),
                IconColumn::make('is_recurring_yearly')->boolean()->label('Yearly'),
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
            ->defaultSort('starts_on');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit'   => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}