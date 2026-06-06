<?php

namespace App\Filament\Resources\KitchenSinks\Pages;

use App\Filament\Resources\KitchenSinks\KitchenSinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKitchenSinks extends ListRecords
{
    protected static string $resource = KitchenSinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
