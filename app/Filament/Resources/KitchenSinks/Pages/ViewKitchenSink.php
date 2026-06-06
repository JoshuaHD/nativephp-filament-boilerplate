<?php

namespace App\Filament\Resources\KitchenSinks\Pages;

use App\Filament\Resources\KitchenSinks\KitchenSinkResource;
use App\Filament\Resources\Pages\MobileViewRecord;
use Filament\Actions\EditAction;

class ViewKitchenSink extends MobileViewRecord
{
    protected static string $resource = KitchenSinkResource::class;

    protected function getMobileHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(fn (): string => KitchenSinkResource::getUrl('edit', [
                    'record' => $this->getRecord(),
                    'back' => KitchenSinkResource::getUrl('view', ['record' => $this->getRecord()]),
                ]))
                ->icon('heroicon-o-pencil-square')
                ->iconButton()
                ->hiddenLabel(),
        ];
    }
}
