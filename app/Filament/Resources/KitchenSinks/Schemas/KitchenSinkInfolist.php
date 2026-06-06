<?php

namespace App\Filament\Resources\KitchenSinks\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KitchenSinkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Overview')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('category'),
                        TextEntry::make('visibility')->badge(),
                        TextEntry::make('priority'),
                        ColorEntry::make('favorite_color'),
                        IconEntry::make('is_active')->boolean(),
                        IconEntry::make('requires_follow_up')->boolean(),
                        TextEntry::make('published_at')->dateTime(),
                        TextEntry::make('event_date')->date(),
                        TextEntry::make('event_time'),
                    ]),
                Section::make('Contact')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('owner_email'),
                        TextEntry::make('support_phone'),
                        TextEntry::make('website')->url(fn (?string $state): ?string => $state),
                    ]),
                Section::make('Collections')
                    ->schema([
                        ImageEntry::make('hero_image')
                            ->disk('public')
                            ->columnSpanFull(),
                        TextEntry::make('tags')
                            ->badge()
                            ->listWithLineBreaks(),
                        TextEntry::make('audiences')
                            ->badge()
                            ->listWithLineBreaks(),
                        TextEntry::make('review_groups')
                            ->badge()
                            ->listWithLineBreaks(),
                        TextEntry::make('delivery_channels')
                            ->badge()
                            ->listWithLineBreaks(),
                        KeyValueEntry::make('metadata')
                            ->columnSpanFull(),
                        RepeatableEntry::make('stats')
                            ->schema([
                                TextEntry::make('label'),
                                TextEntry::make('value'),
                                TextEntry::make('trend')->badge(),
                            ])
                            ->columnSpanFull(),
                        RepeatableEntry::make('faq_items')
                            ->schema([
                                TextEntry::make('question')->weight('bold'),
                                TextEntry::make('answer'),
                                IconEntry::make('highlight')->boolean(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Editors')
                    ->schema([
                        TextEntry::make('summary')->columnSpanFull(),
                        TextEntry::make('notes')->columnSpanFull(),
                        TextEntry::make('content')->html()->columnSpanFull(),
                        TextEntry::make('markdown_content')->columnSpanFull(),
                        TextEntry::make('code_snippet')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
