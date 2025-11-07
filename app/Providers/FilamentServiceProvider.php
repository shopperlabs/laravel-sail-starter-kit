<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Actions;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Forms\Components;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\ImageColumn;
use Filament\View\PanelsIconAlias;
use Illuminate\Support\ServiceProvider;

final class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->replaceFilamentDefaultIcons();
        $this->configureTableActions();
        $this->configureInputs();
    }

    private function configureTableActions(): void
    {
        Actions\CreateAction::configureUsing(
            fn (Actions\Action $action): Actions\Action => $action->modalWidth(Width::ExtraLarge)->slideOver()
        );

        Actions\EditAction::configureUsing(
            fn (Actions\Action $action): Actions\Action => $action->iconButton()
                ->modalWidth(Width::ExtraLarge)
                ->slideOver()
        );

        Actions\DeleteAction::configureUsing(
            fn (Actions\Action $action): Actions\Action => $action->iconButton()
        );
    }

    private function replaceFilamentDefaultIcons(): void
    {
        FilamentIcon::register([
            ActionsIconAlias::DELETE_ACTION => 'untitledui-trash-03',
            ActionsIconAlias::EDIT_ACTION => 'untitledui-edit-03',
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => 'untitledui-flex-align-right',
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => 'untitledui-flex-align-left',
            PanelsIconAlias::PAGES_DASHBOARD_NAVIGATION_ITEM => 'untitledui-home-line',
        ]);
    }

    private function configureInputs(): void
    {
        Components\Field::configureUsing(
            fn (Components\Field $field): Components\Field => $field
                ->uniqueValidationIgnoresRecordByDefault(false)
        );

        Components\SpatieMediaLibraryFileUpload::configureUsing(
            fn (Components\SpatieMediaLibraryFileUpload $spatieFileUpload): Components\SpatieMediaLibraryFileUpload => $spatieFileUpload
                ->visibility('public')
        );

        Components\FileUpload::configureUsing(
            fn (Components\FileUpload $fileUpload): Components\FileUpload => $fileUpload->visibility('public')
        );

        ImageColumn::configureUsing(
            fn (ImageColumn $imageColumn): ImageColumn => $imageColumn->visibility('public')
        );

        ImageEntry::configureUsing(
            fn (ImageEntry $imageEntry): ImageEntry => $imageEntry->visibility('public')
        );
    }
}
