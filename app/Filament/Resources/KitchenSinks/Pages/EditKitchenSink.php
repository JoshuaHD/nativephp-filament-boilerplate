<?php

namespace App\Filament\Resources\KitchenSinks\Pages;

use App\Filament\Resources\KitchenSinks\KitchenSinkResource;
use App\Filament\Resources\Pages\MobileEditRecord;
use Filament\Actions\DeleteAction;

class EditKitchenSink extends MobileEditRecord
{
    protected static string $resource = KitchenSinkResource::class;

    protected function getMobileHeaderActions(): array
    {
        return [
            ...parent::getMobileHeaderActions(),
            DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->iconButton()
                ->hiddenLabel(),
        ];
    }
}
