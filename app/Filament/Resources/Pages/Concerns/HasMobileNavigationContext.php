<?php

namespace App\Filament\Resources\Pages\Concerns;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Js;

trait HasMobileNavigationContext
{
    public ?string $previousUrl = null;

    public ?string $mobileBackUrl = null;

    protected function initializeMobileNavigationContext(string $pageKey): void
    {
        $this->previousUrl = url()->previous();
        $this->mobileBackUrl = request()->string('back')->toString() ?: null;
        $this->extraBodyAttributes['class'] = trim(implode(' ', array_filter([
            $this->extraBodyAttributes['class'] ?? null,
            'fi-mobile-resource-page',
            'fi-mobile-resource-page--constrained',
            "fi-mobile-resource-page--{$pageKey}",
        ])));
    }

    protected function getMobileConstrainedHeader(): View
    {
        return view('filament.resources.pages.mobile-constrained-header', [
            'actions' => $this->getCachedHeaderActions(),
            'backUrl' => $this->getMobileBackUrl(),
            'backClickHandler' => $this->getMobileBackClickHandler(),
            'heading' => $this->getHeading(),
        ]);
    }

    protected function getMobileBackUrl(): string
    {
        if ($this->shouldUseExplicitMobileBackUrl()) {
            return $this->mobileBackUrl;
        }

        if ($this instanceof EditRecord) {
            return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
        }

        return static::getResource()::getUrl();
    }

    protected function shouldUseExplicitMobileBackUrl(): bool
    {
        $appUrl = rtrim(url('/'), '/');

        return filled($this->mobileBackUrl) && str_starts_with($this->mobileBackUrl, $appUrl);
    }

    protected function getMobileBackClickHandler(): string
    {
        $fallbackUrl = Js::from($this->getMobileBackUrl());
        $confirmationMessage = Js::from(__('filament-panels::unsaved-changes-alert.body'));
        $hasDirtyCheck = $this->shouldConfirmUnsavedChangesOnBack() ? 'window.jsMd5(JSON.stringify($wire.data).replace(/\\\\/g, "")) !== $wire.savedDataHash' : 'false';

        return <<<JS
const fallbackUrl = {$fallbackUrl};
const hasChanges = {$hasDirtyCheck};
if (hasChanges && ! confirm({$confirmationMessage})) {
    return;
}
const referrer = document.referrer;
const canGoBack = !! referrer && new URL(referrer).origin === window.location.origin && referrer !== window.location.href;
if (canGoBack) {
    window.history.back();
    return;
}
window.location.href = fallbackUrl;
JS;
    }

    protected function shouldConfirmUnsavedChangesOnBack(): bool
    {
        return method_exists($this, 'hasUnsavedDataChangesAlert') && $this->hasUnsavedDataChangesAlert();
    }
}
