<?php

namespace App\Filament\Resources\TransportRoutes;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\TransportRoutes\Pages\{CreateTransportRoute, EditTransportRoute, ListTransportRoutes};
use App\Filament\Support\LocaleTabs;
use App\Models\TransportRoute;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{Select, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};

class TransportRouteResource extends Resource
{
    protected static ?string $model = TransportRoute::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|UnitEnum|null $navigationGroup = 'Travel info';
    protected static ?int $navigationSort = 3;

    private const MODES = [
        'bus'     => 'Bus',
        'minivan' => 'Minivan',
        'ferry'   => 'Ferry',
        'taxi'    => 'Taxi',
        'flight'  => 'Flight',
        'train'   => 'Train',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Route')->columns(2)->columnSpanFull()->schema([
                Select::make('from_destination_id')
                    ->label('From')
                    ->relationship('fromDestination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                Select::make('to_destination_id')
                    ->label('To')
                    ->relationship('toDestination', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->different('from_destination_id')
                    ->required(),
                Select::make('mode')->options(self::MODES)->required(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
            ]),

            Section::make('Time and price')->columns(4)->columnSpanFull()->schema([
                TextInput::make('duration_min')->label('Duration')->numeric()->suffix('minutes'),
                TextInput::make('price_min')->label('Lowest price')->numeric(),
                TextInput::make('price_max')->label('Highest price')->numeric()->gte('price_min'),
                TextInput::make('currency')->default('USD')->maxLength(3)->required(),
            ]),

            Section::make('Notes')->columnSpanFull()->schema([
                LocaleTabs::make([
                    'notes' => ['label' => 'Tips about this route', 'type' => 'textarea', 'rows' => 4],
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromDestination.name')->label('From'),
                TextColumn::make('toDestination.name')->label('To'),
                TextColumn::make('mode')->badge(),
                TextColumn::make('duration_min')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? intdiv((int) $state, 60) . 'h ' . ((int) $state % 60) . 'm' : '-'),
                TextColumn::make('price_min')
                    ->label('Price')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->price_min === null) {
                            return '-';
                        }

                        $range = number_format((float) $record->price_min, 0);

                        if ($record->price_max !== null) {
                            $range .= '–' . number_format((float) $record->price_max, 0);
                        }

                        return "{$record->currency} {$range}";
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('mode')->options(self::MODES),
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

    public static function getPages(): array
    {
        return [
            'index'  => ListTransportRoutes::route('/'),
            'create' => CreateTransportRoute::route('/create'),
            'edit'   => EditTransportRoute::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}