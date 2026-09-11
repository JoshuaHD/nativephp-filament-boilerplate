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
