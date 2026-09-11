# NativePHP Filament Boilerplate

This project is a Laravel 13 starter configured for:

- NativePHP Mobile v3
- Filament v5
- A root-path Filament admin panel
- A local-only auto-login middleware for development

## Recreate the setup

1. Install the PHP and JS dependencies.

```bash
composer require nativephp/mobile:^3.3 filament/filament:^5.0
npm install axios
```

2. Set the required NativePHP environment values in `.env`.

```dotenv
NATIVEPHP_APP_ID=com.yourcompany.yourapp
NATIVEPHP_APP_VERSION="DEBUG"
NATIVEPHP_APP_VERSION_CODE="1"
```

3. Install the NativePHP shell with ICU support.

```bash
php artisan native:install --with-icu
```

This repo also keeps the generated `native` launcher, `nativephp.lock`, and `config/nativephp.php`.

4. Install Filament with panels enabled.

```bash
php artisan filament:install --panels
```

5. Make the `User` model Filament-accessible.

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
```

6. Create the panel provider and serve it from the root path.

In [app/Providers/Filament/AdminPanelProvider.php](./app/Providers/Filament/AdminPanelProvider.php), set:

```php
->path('')
->viteTheme('resources/css/filament/admin/theme.css')
```

The provider is also registered in [bootstrap/providers.php](./bootstrap/providers.php).

7. Add the NativePHP Vite plugin and the Filament theme entry.

```js
import { nativephpMobile, nativephpHotFile } from './vendor/nativephp/mobile/resources/js/vite-plugin.js';

laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/filament/admin/theme.css',
    ],
    refresh: true,
    hotFile: nativephpHotFile(),
}),
nativephpMobile(),
```

This is reflected in [vite.config.js](./vite.config.js).

8. Add the Filament theme file with NativePHP safe-area padding.

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';

body {
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
}
```

The current theme file is [resources/css/filament/admin/theme.css](./resources/css/filament/admin/theme.css).

9. Add a local-only middleware that auto-logs a development user into Filament.

The implementation lives in [app/Http/Middleware/AutoLoginLocalUser.php](./app/Http/Middleware/AutoLoginLocalUser.php) and is attached in the panel provider middleware stack.

10. Route the app through Filament at `/`.

[routes/web.php](./routes/web.php) intentionally does not register a separate welcome-page route because the Filament panel handles the root path.

## Adding dashboard tiles

Dashboard tiles are Filament widgets. The panel provider already discovers widgets from `app/Filament/Widgets`, so a new widget does not need a separate registration step.

For a set of statistics, generate a stats overview widget:

```bash
php artisan make:filament-widget OrdersOverview --stats-overview --no-interaction
```

Edit the generated `app/Filament/Widgets/OrdersOverview.php` file:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Orders', Order::query()->count())
                ->description('All orders')
                ->color('success'),
            Stat::make('Revenue', '$'.number_format(Order::query()->sum('total'), 2))
                ->description('Recorded revenue')
                ->color('primary'),
        ];
    }
}
```

The widget appears on the default dashboard through `discoverWidgets()`. For other tile types, use the same command with `--chart` or `--table` and implement the generated class. Keep queries in the widget class and return only the values needed by the tile.

## Creating a new resource form

Filament resources provide the list, create, view, and edit pages for an Eloquent model. Generate the model, migration, factory, and resource together when starting a new feature:

```bash
php artisan make:filament-resource Order --model --migration --factory --no-interaction
```

Run the migration, then define the fields in the generated schema file:

```bash
php artisan migrate --no-interaction
```

Edit `app/Filament/Resources/Orders/Schemas/OrderForm.php`:

```php
<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required()
                    ->maxLength(50),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                TextInput::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
            ]);
    }
}
```

The generated resource's `form()` method connects this schema to both the create and edit pages. Add authorization in the model policy, keep validation on the fields, and add casts or relationships to the model as needed. Filament discovers resources from `app/Filament/Resources`, so no route registration is required.

For the compact mobile resource header used by this starter, change the generated page base classes:

```php
use App\Filament\Resources\Pages\MobileCreateRecord;

class CreateOrder extends MobileCreateRecord
{
    protected static string $resource = OrderResource::class;
}
```

Use `MobileEditRecord` and `MobileViewRecord` for the corresponding pages. These wrappers keep the save and back actions in the mobile-safe header. If the standard Filament page layout is preferable, leave the generated `CreateRecord`, `EditRecord`, and `ViewRecord` base classes unchanged.

## Android ICU data

The Android runtime uses ICU 77.1, but its bundled data lacks currency-formatting resources. Install the matching full data before building:

```bash
php artisan native:install --with-icu --no-interaction
php artisan app:install-icu-data --no-interaction
XDEBUG_MODE=off php artisan native:run android emulator-5554 --no-interaction --no-tty
```

The setup command downloads the pinned official ICU 77.1 archive, verifies its SHA-512 checksum, and installs only `icudt77l.dat` and `LICENSE` into `resources/icu/`. It verifies existing files before skipping the download and returns a failure on download or checksum errors. Generated data is ignored by Git, so run the command on each fresh checkout or build environment.

`AppServiceProvider` sets `ICU_DATA` on Android only when `intl` is loaded and the data filename matches the runtime's ICU major version. The pinned version matches the embedded Android runtime, not the host PHP installation; revisit it when upgrading the runtime.

Verify currency output in the compiled Android app, since desktop PHP tests and `extension_loaded('intl')` alone do not detect missing runtime resources.
