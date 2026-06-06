<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\HasMobileNavigationContext;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

abstract class MobileViewRecord extends ViewRecord
{
    use HasMobileNavigationContext;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->initializeMobileNavigationContext('view');
    }

    public function getHeader(): ?View
    {
        return $this->getMobileConstrainedHeader();
    }

    final protected function getHeaderActions(): array
    {
        return $this->getMobileHeaderActions();
    }

    protected function getMobileHeaderActions(): array
    {
        return parent::getHeaderActions();
    }
}
