<?php

namespace App\Filament\Resources\Phrases;

use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Phrases\Pages\{CreatePhrase, EditPhrase, ListPhrases};
use App\Models\Phrase;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction, RestoreAction, RestoreBulkAction};
use Filament\Forms\Components\{Select, SpatieMediaLibraryFileUpload, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter, TrashedFilter};

class PhraseResource extends Resource
{
    protected static ?string $model = Phrase::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-language';
    protected static string|UnitEnum|null $navigationGroup = 'Travel info';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Phrase')->columns(2)->columnSpanFull()->schema([
                Select::make('phrase_category_id')
                    ->label('Category')
                    ->relationship('category', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'])
                    ->default('draft')
                    ->required(),
                TextInput::make('khmer_text')->label('Khmer')->required()->columnSpanFull(),
                TextInput::make('romanization')->helperText('How to pronounce it, in Latin letters')->columnSpanFull(),
                Toggle::make('is_emergency')->label('Emergency phrase'),
            ]),

            Section::make('Translations')->columns(3)->columnSpanFull()->schema([
                TextInput::make('translations.en')->label('English')->required(),
                TextInput::make('translations.zh')->label('中文'),
                TextInput::make('translations.ko')->label('한국어'),
            ]),

            Section::make('Audio')->columnSpanFull()->schema([
                SpatieMediaLibraryFileUpload::make('audio')
                    ->collection('audio')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/ogg'])
                    ->maxSize(5120)
                    ->downloadable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('khmer_text')->label('Khmer')->searchable(),
                TextColumn::make('romanization')->searchable(),
                TextColumn::make('translations.en')->label('English'),
                TextColumn::make('category.name')->label('Category')->badge(),
                IconColumn::make('is_emergency')->boolean()->label('Emergency'),
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
                TernaryFilter::make('is_emergency')->label('Emergency'),
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
            'index'  => ListPhrases::route('/'),
            'create' => CreatePhrase::route('/create'),
            'edit'   => EditPhrase::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}