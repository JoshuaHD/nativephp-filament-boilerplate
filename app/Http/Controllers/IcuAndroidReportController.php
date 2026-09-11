<?php

namespace App\Http\Controllers;

use Composer\InstalledVersions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Locale;
use NumberFormatter;
use Throwable;

class IcuAndroidReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $reproAttempts = [
            $this->runFormatterAttempt(locale: 'en', style: NumberFormatter::DECIMAL, sample: 1234.56),
            $this->runFormatterAttempt(locale: 'en_US', style: NumberFormatter::DECIMAL, sample: 1234.56),
            $this->runFormatterAttempt(locale: 'en', style: NumberFormatter::CURRENCY, sample: 1234.56, currency: 'USD'),
        ];

        $failedAttempts = array_values(array_filter(
            $reproAttempts,
            fn (array $attempt): bool => ! $attempt['constructor_ok'],
        ));

        $report = [
            'summary' => [
                'status' => $failedAttempts === [] ? 'pass' : 'fail',
                'likely_source' => $failedAttempts === [] ? 'app_or_configuration' : 'embedded_php_runtime',
                'minimal_repro' => 'new NumberFormatter("en", NumberFormatter::DECIMAL)',
                'recommended_issue_title' => 'Android embedded PHP intl constructor fails under PHP SAPI embed',
            ],
            'runtime' => [
                'php_sapi' => PHP_SAPI,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'nativephp_mobile_version' => InstalledVersions::getPrettyVersion('nativephp/mobile'),
                'app_locale' => app()->getLocale(),
                'default_locale' => class_exists(Locale::class) ? Locale::getDefault() : null,
                'intl_default_locale_ini' => ini_get('intl.default_locale') ?: null,
                'intl_use_exceptions' => ini_get('intl.use_exceptions') ?: null,
                'request_path' => $request->path(),
            ],
            'intl' => [
                'extension_loaded' => extension_loaded('intl'),
                'extension_version' => phpversion('intl') ?: null,
                'icu_version' => defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : null,
                'icu_data_version' => defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : null,
                'classes' => [
                    'NumberFormatter' => class_exists(NumberFormatter::class),
                    'Locale' => class_exists(Locale::class),
                    'IntlException' => class_exists('IntlException'),
                ],
                'functions' => [
                    'locale_get_default' => function_exists('locale_get_default'),
                    'numfmt_create' => function_exists('numfmt_create'),
                ],
            ],
            'repro_attempts' => $reproAttempts,
            'first_failure' => $failedAttempts[0] ?? null,
        ];

        return response()->json($report, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array{
     *     locale: string,
     *     style: string,
     *     sample: float,
     *     currency: ?string,
     *     constructor_ok: bool,
     *     output: ?string,
     *     formatter_error_code: ?int,
     *     formatter_error_message: ?string,
     *     exception: ?array{class: string, message: string, file: string, line: int}
     * }
     */
    protected function runFormatterAttempt(
        string $locale,
        int $style,
        float $sample,
        ?string $currency = null,
    ): array {
        $result = [
            'locale' => $locale,
            'style' => $this->styleName($style),
            'sample' => $sample,
            'currency' => $currency,
            'constructor_ok' => false,
            'output' => null,
            'formatter_error_code' => null,
            'formatter_error_message' => null,
            'exception' => null,
        ];

        try {
            $formatter = new NumberFormatter($locale, $style);

            $result['constructor_ok'] = true;
            $result['output'] = $currency === null
                ? (string) $formatter->format($sample)
                : (string) $formatter->formatCurrency($sample, $currency);
            $result['formatter_error_code'] = $formatter->getErrorCode();
            $result['formatter_error_message'] = $formatter->getErrorMessage();
        } catch (Throwable $exception) {
            $result['exception'] = [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return $result;
    }

    protected function styleName(int $style): string
    {
        return match ($style) {
            NumberFormatter::DECIMAL => 'DECIMAL',
            NumberFormatter::CURRENCY => 'CURRENCY',
            default => (string) $style,
        };
    }
}
