<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;
use ZipArchive;

class InstallIcuData extends Command
{
    public const string ARCHIVE_URL = 'https://github.com/unicode-org/icu/releases/download/release-77-1/icu4c-77_1-data-bin-l.zip';

    protected const string ARCHIVE_SHA512 = '3de15bb5925956b8e51dc6724c2114a1009ec471a2241b09ae09127f1760f44d02cc29cfbeed6cbaac6ee880553ac8395c61c6043c00ddba3277233e19e6490e';

    /** @var array<string, string> */
    protected const array FILE_SHA512 = [
        'icudt77l.dat' => '1a6d9bbc5eb93260fdefbc0655ecb13d9223fcaed01d04b0f09ab7c83bf25769f0e7ad6717c7dec5ca3b130773c880c50e20dde3d68175cb0f6467fd53c1586a',
        'LICENSE' => 'cbd0daf62556773b179d72a154cf647472713c3c280823e12487a9c7dca3a1865046337cedef5d3b152a6970b61344c98d5248f4e91a2df160ac29f41ee4f6a3',
    ];

    protected $signature = 'app:install-icu-data';

    protected $description = 'Install verified ICU 77.1 data for the Android ICU 77 runtime';

    public function handle(): int
    {
        $archivePath = null;

        try {
            if ($this->isInstalled()) {
                $this->info('ICU 77.1 data and license are already verified.');

                return self::SUCCESS;
            }

            if (! class_exists(ZipArchive::class)) {
                throw new RuntimeException('The PHP zip extension is required to install ICU data.');
            }

            $archivePath = tempnam(sys_get_temp_dir(), 'icu-');

            if ($archivePath === false) {
                throw new RuntimeException('Unable to create a temporary ICU archive.');
            }

            $this->info('Downloading official ICU 77.1 data...');
            Http::connectTimeout(15)->timeout(180)->retry(3, 1000)
                ->withOptions(['sink' => $archivePath])
                ->get(self::ARCHIVE_URL)->throw();

            if (hash_file('sha512', $archivePath) !== static::ARCHIVE_SHA512) {
                throw new RuntimeException('ICU archive SHA-512 checksum mismatch.');
            }

            $this->installArchive($archivePath);
            $this->info('Installed verified ICU 77.1 data and license into resources/icu.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (is_string($archivePath) && is_file($archivePath)) {
                File::delete($archivePath);
            }
        }
    }

    private function isInstalled(): bool
    {
        foreach (static::FILE_SHA512 as $filename => $checksum) {
            $path = resource_path('icu/'.$filename);

            if (! is_file($path) || hash_file('sha512', $path) !== $checksum) {
                return false;
            }
        }

        return true;
    }

    private function installArchive(string $archivePath): void
    {
        $archive = new ZipArchive;

        if ($archive->open($archivePath) !== true) {
            throw new RuntimeException('Unable to open the ICU archive.');
        }

        try {
            $files = [];

            foreach (static::FILE_SHA512 as $filename => $checksum) {
                $contents = $archive->getFromName($filename);

                if ($contents === false || hash('sha512', $contents) !== $checksum) {
                    throw new RuntimeException("ICU file checksum mismatch: {$filename}.");
                }

                $files[$filename] = $contents;
            }

            File::ensureDirectoryExists(resource_path('icu'));

            foreach ($files as $filename => $contents) {
                File::replace(resource_path('icu/'.$filename), $contents);
            }
        } finally {
            $archive->close();
        }
    }
}
