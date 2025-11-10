<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Models\User;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'untitledui-users';

    public static function getNavigationGroup(): string
    {
        return __('filament-shield::filament-shield.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        /** @var User $authenticate */
        $authenticate = Auth::user();

        return $schema
            ->components([
                Components\Hidden::make('email_verified_at')
                    ->default(fn (): string => now()->toDateTimeString())
                    ->visibleOn('create'),
                Components\TextInput::make('firstname')
                    ->label(__('forms.labels.firstname'))
                    ->required(),
                Components\TextInput::make('lastname')
                    ->label(__('forms.labels.lastname'))
                    ->required(),
                Components\TextInput::make('email')
                    ->label(__('forms.labels.email'))
                    ->email()
                    ->required(),
                Components\TextInput::make('password')
                    ->label(__('forms.labels.password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->hiddenOn('edit'),
                Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->required(fn (): bool => $authenticate->hasRole(Utils::getSuperAdminName()))
                    ->visible(fn (): bool => $authenticate->hasRole(Utils::getSuperAdminName()))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles'))
            ->columns([
                TextColumn::make('firstname')
                    ->label(__('forms.labels.firstname'))
                    ->searchable(),
                TextColumn::make('lastname')
                    ->label(__('forms.labels.lastname'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('forms.labels.email'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('filament-shield::filament-shield.resource.label.roles'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('forms.labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (User $record): bool => $record->id !== auth()->id(),
            )
            ->recordActions([
                Actions\EditAction::make()
                    ->visible(fn (User $record): bool => ! $record->is(Auth::user()))
                    ->modalWidth(Width::TwoExtraLarge)
                    ->slideOver(false),
                Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => ! $record->is(Auth::user())),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
