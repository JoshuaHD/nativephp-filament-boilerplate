<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PhpInfoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $extensions = [
            'intl',
            'mbstring',
            'iconv',
            'openssl',
            'pdo_sqlite',
            'sqlite3',
            'curl',
            'zip',
            'fileinfo',
            'sodium',
            'opcache',
            'xdebug',
            'pcntl',
            'posix',
        ];

        return view('diagnostics.php-info', [
            'runtimeFingerprint' => [
                'php_version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'binary' => PHP_BINARY,
                'os_family' => PHP_OS_FAMILY,
                'system' => php_uname(),
                'zend_version' => zend_version(),
                'debug_build' => (bool) ZEND_DEBUG_BUILD,
                'thread_safe' => (bool) ZEND_THREAD_SAFE,
                'loaded_ini_file' => php_ini_loaded_file() ?: null,
                'scanned_ini_files' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) php_ini_scanned_files()),
                ))),
                'user_agent' => (string) $request->userAgent(),
                'host' => (string) $request->getHost(),
                'path' => (string) $request->path(),
            ],
            'extensionChecks' => $this->buildExtensionRows($extensions),
            'classChecks' => $this->buildPresenceRows([
                'PDO',
                'SQLite3',
                'NumberFormatter',
                'IntlDateFormatter',
                'Normalizer',
                'CurlHandle',
                'ZipArchive',
                'FFI',
            ], 'class_exists'),
            'functionChecks' => $this->buildPresenceRows([
                'curl_version',
                'openssl_get_cert_locations',
                'grapheme_strlen',
                'idn_to_ascii',
                'mb_ord',
                'pcntl_fork',
                'posix_getpid',
                'sodium_crypto_secretbox',
            ], 'function_exists'),
            'constantChecks' => $this->buildPresenceRows([
                'PHP_VERSION',
                'PHP_ZTS',
                'ZEND_THREAD_SAFE',
                'ZEND_DEBUG_BUILD',
                'INTL_ICU_VERSION',
                'INTL_ICU_DATA_VERSION',
            ], 'defined'),
            'loadedExtensions' => get_loaded_extensions(),
        ]);
    }

    /**
     * @param  list<string>  $extensions
     * @return list<array{name: string, available: bool, version: ?string}>
     */
    protected function buildExtensionRows(array $extensions): array
    {
        return array_map(function (string $extension): array {
            return [
                'name' => $extension,
                'available' => extension_loaded($extension),
                'version' => phpversion($extension) ?: null,
            ];
        }, $extensions);
    }

    /**
     * @param  list<string>  $items
     * @return list<array{name: string, available: bool}>
     */
    protected function buildPresenceRows(array $items, string $callback): array
    {
        return array_map(fn (string $item): array => [
            'name' => $item,
            'available' => (bool) $callback($item),
        ], $items);
    }
}
