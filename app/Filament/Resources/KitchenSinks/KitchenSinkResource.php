<?php

namespace App\Filament\Resources\KitchenSinks;

use App\Filament\Resources\KitchenSinks\Pages\CreateKitchenSink;
use App\Filament\Resources\KitchenSinks\Pages\EditKitchenSink;
use App\Filament\Resources\KitchenSinks\Pages\ListKitchenSinks;
use App\Filament\Resources\KitchenSinks\Pages\ViewKitchenSink;
use App\Filament\Resources\KitchenSinks\Schemas\KitchenSinkForm;
use App\Filament\Resources\KitchenSinks\Schemas\KitchenSinkInfolist;
use App\Filament\Resources\KitchenSinks\Tables\KitchenSinksTable;
use App\Models\KitchenSink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KitchenSinkResource extends Resource
{
    protected static ?string $model = KitchenSink::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Demos';

    protected static ?string $navigationLabel = 'Kitchen Sinks';

    public static function form(Schema $schema): Schema
    {
        return KitchenSinkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KitchenSinkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenSinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKitchenSinks::route('/'),
            'create' => CreateKitchenSink::route('/create'),
            'view' => ViewKitchenSink::route('/{record}'),
            'edit' => EditKitchenSink::route('/{record}/edit'),
        ];
    }
}
