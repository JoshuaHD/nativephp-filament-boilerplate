<?php

namespace App\Filament\Resources\KitchenSinks\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class KitchenSinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Kitchen Sink Demo')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Overview')
                            ->schema([
                                Section::make('Basics')
                                    ->description('Core text, numeric, toggle, radio, and select inputs.')
                                    ->columns(2)
                                    ->schema([
                                        Placeholder::make('demo_intro')
                                            ->label('About this demo')
                                            ->content('This resource intentionally exercises a broad spread of core Filament input types.'),
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('category')
                                            ->options([
                                                'marketing' => 'Marketing',
                                                'operations' => 'Operations',
                                                'product' => 'Product',
                                                'support' => 'Support',
                                            ])
                                            ->searchable()
                                            ->native(false),
                                        ToggleButtons::make('status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'review' => 'Review',
                                                'published' => 'Published',
                                                'archived' => 'Archived',
                                            ])
                                            ->required()
                                            ->inline()
                                            ->helperText('Single-select button group.'),
                                        Radio::make('visibility')
                                            ->options([
                                                'public' => 'Public',
                                                'internal' => 'Internal',
                                                'private' => 'Private',
                                            ])
                                            ->inline()
                                            ->required(),
                                        TextInput::make('price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->minValue(0)
                                            ->step('0.01'),
                                        Slider::make('progress')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(5),
                                        TextInput::make('priority')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->step(1),
                                        ColorPicker::make('favorite_color'),
                                        Toggle::make('is_active')
                                            ->default(true),
                                        Checkbox::make('requires_follow_up'),
                                        ToggleButtons::make('delivery_channels')
                                            ->label('Delivery channels')
                                            ->multiple()
                                            ->options([
                                                'email' => 'Email',
                                                'push' => 'Push',
                                                'sms' => 'SMS',
                                                'in_app' => 'In-app',
                                            ])
                                            ->helperText('Multi-select button group using the same button style as status.'),
                                    ]),
                                Section::make('Short-form content')
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('summary')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->autosize()
                                            ->helperText('Autosizes as content grows.'),
                                        Textarea::make('notes')
                                            ->rows(6)
                                            ->columnSpanFull()
                                            ->helperText('Fixed height textarea that scrolls once content overflows.'),
                                    ]),
                            ]),
                        Tabs\Tab::make('Scheduling')
                            ->schema([
                                Section::make('Contact and dates')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('owner_email')
                                            ->email(),
                                        TextInput::make('support_phone')
                                            ->tel(),
                                        TextInput::make('website')
                                            ->url()
                                            ->prefix('https://'),
                                        DateTimePicker::make('published_at'),
                                        DatePicker::make('event_date'),
                                        TimePicker::make('event_time')
                                            ->seconds(false),
                                        DateTimePicker::make('reminder_at'),
                                    ]),
                            ]),
                        Tabs\Tab::make('Collections')
                            ->schema([
                                Section::make('Files and structured data')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('hero_image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('kitchen-sinks/hero-images')
                                            ->columnSpanFull(),
                                        FileUpload::make('attachments')
                                            ->disk('public')
                                            ->directory('kitchen-sinks/attachments')
                                            ->multiple()
                                            ->downloadable()
                                            ->openable()
                                            ->columnSpanFull(),
                                        TagsInput::make('tags')
                                            ->splitKeys(['Tab', ',', ' '])
                                            ->default([]),
                                        CheckboxList::make('audiences')
                                            ->options([
                                                'customers' => 'Customers',
                                                'staff' => 'Staff',
                                                'partners' => 'Partners',
                                                'vendors' => 'Vendors',
                                            ])
                                            ->columns(2)
                                            ->default([])
                                            ->helperText('Multi-select using checkboxes.'),
                                        Select::make('review_groups')
                                            ->multiple()
                                            ->options([
                                                'design' => 'Design',
                                                'legal' => 'Legal',
                                                'finance' => 'Finance',
                                                'leadership' => 'Leadership',
                                            ])
                                            ->searchable()
                                            ->native(false)
                                            ->default([])
                                            ->helperText('Searchable multi-select dropdown with predefined options.'),
                                        KeyValue::make('metadata')
                                            ->keyLabel('Key')
                                            ->valueLabel('Value')
                                            ->addActionLabel('Add metadata')
                                            ->reorderable()
                                            ->columnSpanFull(),
                                        Repeater::make('stats')
                                            ->addActionLabel('Add stat')
                                            ->defaultItems(1)
                                            ->reorderableWithButtons()
                                            ->helperText('Each item combines a label, numeric value, and trend selector.')
                                            ->schema([
                                                TextInput::make('label')->required(),
                                                TextInput::make('value')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->step('0.01')
                                                    ->required()
                                                    ->helperText('Numeric input inside a repeater item.'),
                                                Select::make('trend')
                                                    ->options([
                                                        'up' => 'Up',
                                                        'flat' => 'Flat',
                                                        'down' => 'Down',
                                                    ])
                                                    ->required()
                                                    ->native(false),
                                            ])
                                            ->columnSpanFull(),
                                        Repeater::make('faq_items')
                                            ->addActionLabel('Add FAQ item')
                                            ->defaultItems(1)
                                            ->schema([
                                                TextInput::make('question')->required(),
                                                Textarea::make('answer')->rows(3)->required(),
                                                Toggle::make('highlight'),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Editors')
                            ->schema([
                                Section::make('Long-form editors')
                                    ->schema([
                                        RichEditor::make('content')
                                            ->mergeTags([
                                                'name' => 'Name',
                                                'today' => 'Today',
                                            ])
                                            ->columnSpanFull(),
                                        MarkdownEditor::make('markdown_content')
                                            ->columnSpanFull(),
                                        CodeEditor::make('code_snippet')
                                            ->language(Language::Php)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
