<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Alignment;

class Dashboard extends BaseDashboard
{
    protected ?Alignment $headerActionsAlignment = Alignment::End;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('phpInfo')
                ->label('PHP Info')
                ->icon('heroicon-o-code-bracket')
                ->url(route('diagnostics.php-info')),
            Action::make('icuProbe')
                ->label('ICU Test')
                ->icon('heroicon-o-bug-ant')
                ->url(route('diagnostics.icu-probe')),
            Action::make('icuCheck')
                ->label('ICU Report')
                ->icon('heroicon-o-bug-ant')
                ->url(route('diagnostics.icu-check')),
        ];
    }
}
