<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Locale;
use NumberFormatter;
use Throwable;

class IcuProbeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $amount = 1234.56;
        $ratio = 0.1234;
        $wholeNumber = 1234;
        $ordinalNumber = 21;
        $locales = ['en_US', 'en', 'de_DE', 'fr_FR', 'nl_NL'];
        $currencies = ['USD', 'EUR', 'CAD'];
        $styleRows = [];

        foreach ($locales as $locale) {
            $styleRows[] = $this->probeNativeStyle(
                locale: $locale,
                label: 'DECIMAL',
                style: NumberFormatter::DECIMAL,
                value: $amount,
                laravelOutput: $this->runProbe(
                    fn (): string|false => Number::format($amount, locale: $locale),
                ),
            );

            $styleRows[] = $this->probeNativeStyle(
                locale: $locale,
                label: 'PERCENT',
                style: NumberFormatter::PERCENT,
                value: $ratio,
            );

            $styleRows[] = $this->probeNativeStyle(
                locale: $locale,
                label: 'SCIENTIFIC',
                style: NumberFormatter::SCIENTIFIC,
                value: $amount,
            );

            $styleRows[] = $this->probeNativeStyle(
                locale: $locale,
                label: 'SPELLOUT',
                style: NumberFormatter::SPELLOUT,
                value: $wholeNumber,
            );

            if (defined(NumberFormatter::class.'::ORDINAL')) {
                $styleRows[] = $this->probeNativeStyle(
                    locale: $locale,
                    label: 'ORDINAL',
                    style: NumberFormatter::ORDINAL,
                    value: $ordinalNumber,
                );
            }

            foreach ($currencies as $currency) {
                $styleRows[] = $this->probeNativeStyle(
                    locale: $locale,
                    label: 'CURRENCY',
                    style: NumberFormatter::CURRENCY,
                    value: $amount,
                    currency: $currency,
                    laravelOutput: $this->runProbe(
                        fn (): string|false => Number::currency($amount, in: $currency, locale: $locale),
                    ),
                );
            }
        }

        $problemRows = array_values(array_filter(
            $styleRows,
            fn (array $row): bool => $row['status'] !== 'ok',
        ));

        return view('diagnostics.icu-probe', [
            'intlLoaded' => extension_loaded('intl'),
            'icuVersion' => defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : null,
            'icuDataVersion' => defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : null,
            'intlVersion' => phpversion('intl') ?: null,
            'defaultLocale' => Locale::getDefault(),
            'capabilityChecks' => [
                'extensions' => $this->buildCapabilityRows([
                    'intl',
                    'mbstring',
                    'iconv',
                ]),
                'classes' => $this->buildCapabilityRows([
                    NumberFormatter::class,
                    Locale::class,
                    'IntlDateFormatter',
                    'Normalizer',
                    'ResourceBundle',
                ], 'class_exists'),
                'functions' => $this->buildCapabilityRows([
                    'locale_get_default',
                    'grapheme_strlen',
                    'idn_to_ascii',
                    'normalizer_is_normalized',
                ], 'function_exists'),
                'constants' => $this->buildCapabilityRows([
                    'INTL_ICU_VERSION',
                    'INTL_ICU_DATA_VERSION',
                    'INTL_MAX_LOCALE_LEN',
                ], 'defined'),
            ],
            'runtimeFingerprint' => [
                'php_version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'user_agent' => (string) $request->userAgent(),
                'host' => (string) $request->getHost(),
                'path' => (string) $request->path(),
            ],
            'summary' => [
                'total_rows' => count($styleRows),
                'problem_rows' => count($problemRows),
                'constructor_failures' => count(array_filter(
                    $styleRows,
                    fn (array $row): bool => ! $row['native_formatter']['ok'],
                )),
                'locale_fallbacks' => count(array_filter(
                    $styleRows,
                    fn (array $row): bool => $row['native_formatter']['ok']
                        && $row['native_formatter']['actual_locale'] !== null
                        && $row['native_formatter']['actual_locale'] !== $row['expected_locale'],
                )),
            ],
            'problemRows' => $problemRows,
            'styleRows' => $styleRows,
        ]);
    }

    /**
     * @return array{ok: bool, output: string}
     */
    protected function runProbe(callable $callback): array
    {
        try {
            $result = $callback();

            return [
                'ok' => true,
                'output' => (string) $result,
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'output' => $exception::class.': '.$exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{
     *     locale: string,
     *     label: string,
     *     sample: string,
     *     currency: ?string,
     *     expected_locale: string,
     *     expected_output: ?string,
     *     expected_output_mode: 'server'|'browser'|'unsupported',
     *     status: 'ok'|'warning'|'fail',
     *     native_formatter: array{
     *         ok: bool,
     *         output: string,
     *         valid_locale: ?string,
     *         actual_locale: ?string,
     *         decimal_symbol: ?string,
     *         grouping_symbol: ?string
     *     },
     *     laravel_number: array{ok: bool, output: string}|null
     * }
     */
    protected function probeNativeStyle(
        string $locale,
        string $label,
        int $style,
        int|float $value,
        ?string $currency = null,
        ?array $laravelOutput = null,
    ): array {
        $nativeFormatter = $this->runFormatterProbe(
            locale: $locale,
            style: $style,
            value: $value,
            currency: $currency,
        );

        return [
            'locale' => $locale,
            'label' => $label,
            'sample' => (string) $value,
            'currency' => $currency,
            'expected_locale' => $this->expectedLocaleFor($locale),
            'expected_output' => $this->expectedOutputFor(
                locale: $locale,
                label: $label,
                currency: $currency,
            ),
            'expected_output_mode' => $this->expectedOutputModeFor($label),
            'status' => $this->determineStatus(
                expectedLocale: $locale,
                nativeFormatter: $nativeFormatter,
            ),
            'native_formatter' => $nativeFormatter,
            'laravel_number' => $laravelOutput,
        ];
    }

    /**
     * @return array{
     *     ok: bool,
     *     output: string,
     *     valid_locale: ?string,
     *     actual_locale: ?string,
     *     decimal_symbol: ?string,
     *     grouping_symbol: ?string
     * }
     */
    protected function runFormatterProbe(
        string $locale,
        int $style,
        int|float $value,
        ?string $currency = null,
    ): array {
        try {
            $formatter = new NumberFormatter($locale, $style);
            $output = $currency === null
                ? $formatter->format($value)
                : $formatter->formatCurrency((float) $value, $currency);

            return [
                'ok' => true,
                'output' => (string) $output,
                'valid_locale' => $formatter->getLocale(Locale::VALID_LOCALE),
                'actual_locale' => $formatter->getLocale(Locale::ACTUAL_LOCALE),
                'decimal_symbol' => $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL),
                'grouping_symbol' => $formatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'output' => $exception::class.': '.$exception->getMessage(),
                'valid_locale' => null,
                'actual_locale' => null,
                'decimal_symbol' => null,
                'grouping_symbol' => null,
            ];
        }
    }

    protected function expectedLocaleFor(string $locale): string
    {
        return $locale;
    }

    protected function expectedOutputFor(
        string $locale,
        string $label,
        ?string $currency = null,
    ): ?string {
        return match ($label) {
            'DECIMAL' => match ($locale) {
                'en_US', 'en' => '1,234.56',
                'de_DE', 'nl_NL' => '1.234,56',
                'fr_FR' => '1 234,56',
                default => null,
            },
            'PERCENT' => match ($locale) {
                'en_US', 'en' => '12%',
                'de_DE', 'fr_FR', 'nl_NL' => '12 %',
                default => null,
            },
            default => null,
        };
    }

    protected function expectedOutputModeFor(string $label): string
    {
        return match ($label) {
            'DECIMAL', 'PERCENT' => 'server',
            'SCIENTIFIC', 'CURRENCY' => 'browser',
            default => 'unsupported',
        };
    }

    /**
     * @param array{
     *     ok: bool,
     *     output: string,
     *     valid_locale: ?string,
     *     actual_locale: ?string,
     *     decimal_symbol: ?string,
     *     grouping_symbol: ?string
     * } $nativeFormatter
     */
    protected function determineStatus(string $expectedLocale, array $nativeFormatter): string
    {
        if (! $nativeFormatter['ok']) {
            return 'fail';
        }

        if (($nativeFormatter['actual_locale'] !== null) && ($nativeFormatter['actual_locale'] !== $expectedLocale)) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * @param  list<string>  $items
     * @return list<array{name: string, available: bool, version: ?string}>
     */
    protected function buildCapabilityRows(array $items, string $callback = 'extension_loaded'): array
    {
        return array_map(function (string $item) use ($callback): array {
            $available = (bool) $callback($item);

            return [
                'name' => $item,
                'available' => $available,
                'version' => $callback === 'extension_loaded'
                    ? (phpversion($item) ?: null)
                    : null,
            ];
        }, $items);
    }
}
