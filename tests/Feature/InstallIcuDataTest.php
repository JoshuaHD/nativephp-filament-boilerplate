<?php

use App\Console\Commands\InstallIcuData;
use App\Providers\AppServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class FixtureInstallIcuData extends InstallIcuData
{
    protected const string ARCHIVE_SHA512 = '8c2343c34d1141ca8ec48e522fcec87c0672970249659beadbdfaafeda50c596dac0a6bc487231def2e9a42ecf56ba58f9ad419452fa0d1021a10ce6c000c319';

    protected const array FILE_SHA512 = [
        'icudt77l.dat' => '0e1e21ecf105ec853d24d728867ad70613c21663a4693074b2a3619c1bd39d66b588c33723bb466c72424e80e3ca63c249078ab347bab9428500e7ee43059d0d',
        'LICENSE' => '46b4f8536ee51e63feecfb920a005009b95d2594d41b54daa290f709145a6023f3ce6c8d86562dbabdb1412c8720cc5ed3ac34e93e81e8e4b1cb68b85e96f9c8',
    ];
}

beforeEach(function () {
    $this->originalBasePath = base_path();
    $this->temporaryResources = sys_get_temp_dir().'/icu-test-'.bin2hex(random_bytes(8));
    $this->app->make(Kernel::class)->registerCommand(new FixtureInstallIcuData);
    $this->app->setBasePath($this->temporaryResources);
    Http::preventStrayRequests();
    $this->archive = base64_decode('UEsDBBQAAAAAAAAAIQCyrgjTCQAAAAkAAAAMAAAAaWN1ZHQ3N2wuZGF0dGVzdCBkYXRhUEsDBBQAAAAAAAAAIQDto1n+DAAAAAwAAAAHAAAATElDRU5TRXRlc3QgbGljZW5zZVBLAwQUAAAAAAAAACEAAl+pHg0AAAANAAAADAAAAHVud2FudGVkLnR4dG5vdCBpbnN0YWxsZWRQSwECFAMUAAAAAAAAACEAsq4I0wkAAAAJAAAADAAAAAAAAAAAAAAAgAEAAAAAaWN1ZHQ3N2wuZGF0UEsBAhQDFAAAAAAAAAAhAO2jWf4MAAAADAAAAAcAAAAAAAAAAAAAAIABMwAAAExJQ0VOU0VQSwECFAMUAAAAAAAAACEAAl+pHg0AAAANAAAADAAAAAAAAAAAAAAAgAFkAAAAdW53YW50ZWQudHh0UEsFBgAAAAADAAMAqQAAAJsAAAAAAA==');
});

afterEach(function () {
    $this->app->setBasePath($this->originalBasePath);
    File::deleteDirectory($this->temporaryResources);
});

test('installs only verified data and license and skips a second download', function () {
    Http::fake([InstallIcuData::ARCHIVE_URL => Http::response($this->archive)]);

    $this->artisan('app:install-icu-data')->assertSuccessful();

    expect(File::get(resource_path('icu/icudt77l.dat')))->toBe('test data')
        ->and(File::get(resource_path('icu/LICENSE')))->toBe('test license')
        ->and(File::exists(resource_path('icu/unwanted.txt')))->toBeFalse();

    $this->artisan('app:install-icu-data')
        ->expectsOutputToContain('already verified')
        ->assertSuccessful();

    Http::assertSentCount(1);
});

test('repairs corrupted data or a missing license', function (string $filename) {
    File::ensureDirectoryExists(resource_path('icu'));
    File::put(resource_path('icu/icudt77l.dat'), 'test data');
    File::put(resource_path('icu/LICENSE'), 'test license');
    if ($filename === 'LICENSE') {
        File::delete(resource_path('icu/'.$filename));
    } else {
        File::put(resource_path('icu/'.$filename), 'corrupted data');
    }
    Http::fake([InstallIcuData::ARCHIVE_URL => Http::response($this->archive)]);

    $this->artisan('app:install-icu-data')->assertSuccessful();

    expect(File::get(resource_path('icu/icudt77l.dat')))->toBe('test data')
        ->and(File::get(resource_path('icu/LICENSE')))->toBe('test license');
    Http::assertSentCount(1);
})->with(['icudt77l.dat', 'LICENSE']);

test('rejects a corrupted download without overwriting existing files', function () {
    File::ensureDirectoryExists(resource_path('icu'));
    File::put(resource_path('icu/icudt77l.dat'), 'existing data');
    Http::fake([InstallIcuData::ARCHIVE_URL => Http::response('corrupt archive')]);

    $this->artisan('app:install-icu-data')
        ->expectsOutputToContain('SHA-512 checksum mismatch')
        ->assertFailed();

    expect(File::get(resource_path('icu/icudt77l.dat')))->toBe('existing data')
        ->and(File::exists(resource_path('icu/LICENSE')))->toBeFalse();
});

test('fails on an unsuccessful download', function () {
    Http::fake([InstallIcuData::ARCHIVE_URL => Http::response('', 404)]);

    $this->artisan('app:install-icu-data')->assertFailed();

    expect(File::isDirectory(resource_path('icu')))->toBeFalse();
});

test('initializes ICU data only for Android with a matching data file', function (string $platform, bool $matchingFile, bool $shouldInitialize) {
    if (! extension_loaded('intl')) {
        $this->markTestSkipped('The intl extension is required for this initialization test.');
    }

    $originalIcuData = getenv('ICU_DATA');
    putenv('ICU_DATA=/existing-icu-data');
    config(['nativephp-internal.platform' => $platform]);
    $majorVersion = explode('.', INTL_ICU_VERSION)[0];
    File::ensureDirectoryExists(resource_path('icu'));
    File::put(resource_path('icu/icudt'.($matchingFile ? $majorVersion : '0').'l.dat'), 'data');

    try {
        (new AppServiceProvider($this->app))->register();

        expect(getenv('ICU_DATA'))->toBe($shouldInitialize ? resource_path('icu') : '/existing-icu-data');
    } finally {
        putenv($originalIcuData === false ? 'ICU_DATA' : 'ICU_DATA='.$originalIcuData);
    }
})->with([
    'Android matching ICU' => ['android', true, true],
    'Android mismatched ICU' => ['android', false, false],
    'iOS' => ['ios', true, false],
    'desktop' => ['', true, false],
]);
