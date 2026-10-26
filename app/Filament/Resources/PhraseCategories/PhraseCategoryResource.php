<?php

namespace App\Filament\Resources\PhraseCategories;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\PhraseCategories\Pages\{CreatePhraseCategory, EditPhraseCategory, ListPhraseCategories};
use App\Filament\Support\LocaleTabs;
use App\Models\PhraseCategory;
use Filament\Actions\{DeleteAction, EditAction};
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PhraseCategoryResource extends Resource
{
    protected static ?string $model = PhraseCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|UnitEnum|null $navigationGroup = 'Setup';
    protected static ?int $navigationSort = 3;

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
                ->helperText('Lowercase English. Example: greetings'),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('slug'),
                TextColumn::make('phrases_count')->counts('phrases')->label('Phrases'),
                TextColumn::make('sort_order')->label('Order'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn ($record) => $record->phrases()->withTrashed()->exists()),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPhraseCategories::route('/'),
            'create' => CreatePhraseCategory::route('/create'),
            'edit'   => EditPhraseCategory::route('/{record}/edit'),
        ];
    }
}