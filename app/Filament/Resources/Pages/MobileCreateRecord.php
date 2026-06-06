<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\HasMobileNavigationContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

abstract class MobileCreateRecord extends CreateRecord
{
    use HasMobileNavigationContext;

    public function mount(): void
    {
        parent::mount();

        $this->initializeMobileNavigationContext('create');
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
        return [
            $this->getCreateFormAction()
                ->label('Save')
                ->icon('heroicon-o-check')
                ->iconButton()
                ->formId('form'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
