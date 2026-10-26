<?php

namespace App\Filament\Resources\Users;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Support\LocaleTabs;
use App\Models\User;
use Filament\Actions\{DeleteAction, EditAction};
use Filament\Forms\Components\{Select, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|UnitEnum|null $navigationGroup = 'Access';

    // content editors never see this screen
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(150),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->minLength(8)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))   // blank = keep the old password
                ->helperText('Leave empty to keep the current password.'),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload(),
            Select::make('preferred_language')->options(LocaleTabs::LOCALES)->default('en'),
            TextInput::make('country_code')->maxLength(2)->helperText('Two letters, e.g. KH'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->badge(),
                TextColumn::make('country_code')->label('Country'),
                TextColumn::make('last_active_at')->since()->label('Last active')->placeholder('Never'),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}