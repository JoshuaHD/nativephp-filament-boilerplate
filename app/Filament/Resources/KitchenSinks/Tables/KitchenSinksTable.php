<?php

namespace App\Filament\Resources\KitchenSinks\Tables;

use App\Filament\Resources\KitchenSinks\KitchenSinkResource;
use App\Models\KitchenSink;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class KitchenSinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hero_image')
                    ->label('Hero')
                    ->disk('public')
                    ->circular()
                    ->imageHeight(40)
                    ->defaultImageUrl(url('/favicon.ico'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): ?string => $record->category ? ucfirst($record->category) : null)
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('visibility')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'internal' => 'info',
                        'private' => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('progress')
                    ->suffix('%')
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 40 => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                ColorColumn::make('favorite_color')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('requires_follow_up')
                    ->label('Follow up')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),
                TextColumn::make('tags')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('delivery_channels')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('owner_email')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('support_phone')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_time')
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reminder_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('attachments')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('audiences')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('review_groups')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('metadata')
                    ->state(fn (KitchenSink $record): array => collect($record->metadata ?? [])
                        ->map(fn (mixed $value, string $key): string => $key.': '.json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        ->values()->all())
                    ->listWithLineBreaks()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stats')
                    ->state(fn (KitchenSink $record): array => collect($record->stats ?? [])
                        ->map(fn (array $stat): string => ($stat['label'] ?? '').': '.($stat['value'] ?? '').' ('.($stat['trend'] ?? '').')')
                        ->all())
                    ->listWithLineBreaks()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('faq_items')
                    ->label('FAQ items')
                    ->state(fn (KitchenSink $record): array => collect($record->faq_items ?? [])
                        ->map(fn (array $item): string => ($item['question'] ?? '').': '.($item['answer'] ?? '').' (Highlighted: '.(($item['highlight'] ?? false) ? 'Yes' : 'No').')')
                        ->all())
                    ->listWithLineBreaks()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('summary')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('content')
                    ->html()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('markdown_content')
                    ->markdown()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('code_snippet')
                    ->fontFamily('mono')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'marketing' => 'Marketing',
                        'operations' => 'Operations',
                        'product' => 'Product',
                        'support' => 'Support',
                    ]),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->url(fn ($record): string => KitchenSinkResource::getUrl('edit', [
                        'record' => $record,
                        'back' => KitchenSinkResource::getUrl(),
                    ])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
