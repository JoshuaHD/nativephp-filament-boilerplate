<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\HasMobileNavigationContext;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

abstract class MobileEditRecord extends EditRecord
{
    use HasMobileNavigationContext;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->initializeMobileNavigationContext('edit');
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
            $this->getSaveFormAction()
                ->label('Save')
                ->icon('heroicon-o-check')
                ->iconButton()
                ->formId('form'),
            ...parent::getHeaderActions(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
