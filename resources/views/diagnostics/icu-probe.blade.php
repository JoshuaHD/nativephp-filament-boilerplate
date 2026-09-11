<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICU Test</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f6f4ef;
            color: #1f2937;
        }

        main {
            margin: 0 auto;
            max-width: 1100px;
        }

        h1, h2 {
            margin: 0 0 12px;
        }

        p {
            margin: 0 0 16px;
        }

        .panel {
            background: #fff;
            border: 1px solid #d6d3d1;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .page-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .page-nav a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            border: 1px solid #d6d3d1;
            border-radius: 999px;
            background: #fff;
            color: #1f2937;
            text-decoration: none;
            font-weight: 600;
        }

        .page-nav a:hover {
            background: #fafaf9;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .card {
            border: 1px solid #d6d3d1;
            border-radius: 12px;
            padding: 14px;
            background: #fafaf9;
        }

        .card strong {
            display: block;
            margin-bottom: 6px;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.1;
        }

        .card-danger {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .card-warning {
            background: #fff7ed;
            border-color: #fdba74;
        }

        .card-ok {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .meta strong,
        td strong {
            display: block;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 1200px;
        }

        th,
        td {
            text-align: left;
            vertical-align: top;
            padding: 10px 8px;
            border-top: 1px solid #e7e5e4;
        }

        th {
            border-top: 0;
            background: #fafaf9;
        }

        tr.row-warning td {
            background: #fff7ed;
        }

        tr.row-fail td {
            background: #fef2f2;
        }

        .ok {
            color: #166534;
        }

        .fail {
            color: #b91c1c;
        }

        .warning {
            color: #c2410c;
        }

        .muted {
            color: #57534e;
        }

        code {
            font-family: ui-monospace, monospace;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px 4px;
        }

        .runtime-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .runtime-grid code {
            word-break: break-word;
        }

        .expected-output {
            min-width: 180px;
        }
    </style>
</head>
<body>
<main>
    <nav class="page-nav">
        <a href="{{ route('filament.admin.pages.dashboard') }}">Back to dashboard</a>
        <a href="{{ route('diagnostics.php-info') }}">PHP info</a>
        <a href="{{ route('diagnostics.icu-probe') }}">ICU test</a>
    </nav>

    <div class="panel">
        <h1>ICU Test</h1>
        <p>This page exercises ICU formatting directly, outside Filament, and shows the relevant `intl` capabilities needed to understand what is actually available at runtime.</p>

        <div class="meta">
            <div>
                <strong>`intl` loaded</strong>
                <span>{{ $intlLoaded ? 'yes' : 'no' }}</span>
            </div>
            <div>
                <strong>ICU version</strong>
                <span>{{ $icuVersion ?? 'unknown' }}</span>
            </div>
            <div>
                <strong>ICU data version</strong>
                <span>{{ $icuDataVersion ?? 'unknown' }}</span>
            </div>
            <div>
                <strong>`intl` extension version</strong>
                <span>{{ $intlVersion ?? 'unknown' }}</span>
            </div>
            <div>
                <strong>Default locale</strong>
                <span>{{ $defaultLocale }}</span>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>Capability Checks</h2>
        <p class="muted">Use this matrix first to see whether the expected `intl` pieces are actually loaded and defined.</p>

        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Status</th>
                <th>Version</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($capabilityChecks['extensions'] as $row)
                <tr>
                    <td>extension</td>
                    <td><code>{{ $row['name'] }}</code></td>
                    <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'loaded' : 'missing' }}</td>
                    <td>
                        @if ($row['version'])
                            <code>{{ $row['version'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            @foreach ($capabilityChecks['classes'] as $row)
                <tr>
                    <td>class</td>
                    <td><code>{{ $row['name'] }}</code></td>
                    <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'available' : 'missing' }}</td>
                    <td><span class="muted">-</span></td>
                </tr>
            @endforeach
            @foreach ($capabilityChecks['functions'] as $row)
                <tr>
                    <td>function</td>
                    <td><code>{{ $row['name'] }}</code></td>
                    <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'available' : 'missing' }}</td>
                    <td><span class="muted">-</span></td>
                </tr>
            @endforeach
            @foreach ($capabilityChecks['constants'] as $row)
                <tr>
                    <td>constant</td>
                    <td><code>{{ $row['name'] }}</code></td>
                    <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'defined' : 'missing' }}</td>
                    <td><span class="muted">-</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="panel">
        <h2>Problems First</h2>
        <p class="muted">These cards summarize what is broken in the current runtime, before the full table.</p>

        <div class="cards">
            <div class="card card-danger">
                <strong>Constructor failures</strong>
                <div class="card-value">{{ $summary['constructor_failures'] }}</div>
                <div class="muted">Rows where native `NumberFormatter` construction failed outright.</div>
            </div>
            <div class="card card-warning">
                <strong>Locale fallbacks</strong>
                <div class="card-value">{{ $summary['locale_fallbacks'] }}</div>
                <div class="muted">Rows where ICU resolved to a different locale than requested.</div>
            </div>
            <div class="card {{ $summary['problem_rows'] > 0 ? 'card-danger' : 'card-ok' }}">
                <strong>Problem rows</strong>
                <div class="card-value">{{ $summary['problem_rows'] }} / {{ $summary['total_rows'] }}</div>
                <div class="muted">Anything marked red or orange in the matrix below.</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>Runtime Fingerprint</h2>
        <p class="muted">Open this page in Jump and in the emulator. The biggest clue so far is `PHP SAPI`: Jump is using <code>cli-server</code> while the emulator is using <code>embed</code>, which strongly suggests different runtime packaging/execution paths.</p>

        <div class="runtime-grid">
            <div><strong>PHP version</strong> <code>{{ $runtimeFingerprint['php_version'] }}</code></div>
            <div><strong>PHP SAPI</strong> <code>{{ $runtimeFingerprint['sapi'] }}</code></div>
            <div><strong>Host</strong> <code>{{ $runtimeFingerprint['host'] }}</code></div>
            <div><strong>Path</strong> <code>{{ $runtimeFingerprint['path'] }}</code></div>
            <div><strong>User agent</strong> <code>{{ $runtimeFingerprint['user_agent'] }}</code></div>
        </div>
    </div>

    <div class="panel">
        <h2>Failures And Fallbacks</h2>
        <p class="muted">This is the short list to compare first. Red rows are hard failures. Orange rows constructed, but resolved to the wrong locale.</p>
        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Locale</th>
                <th>Style</th>
                <th>Currency</th>
                <th>Should resolve to</th>
                <th>Actual locale</th>
                <th>Should output</th>
                <th>Native `NumberFormatter`</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($problemRows as $row)
                <tr class="row-{{ $row['status'] }}">
                    <td><code>{{ $row['locale'] }}</code></td>
                    <td><code>{{ $row['label'] }}</code></td>
                    <td>
                        @if ($row['currency'])
                            <code>{{ $row['currency'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td><code>{{ $row['expected_locale'] }}</code></td>
                    <td>
                        @if ($row['native_formatter']['actual_locale'])
                            <code>{{ $row['native_formatter']['actual_locale'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td class="expected-output">
                        @if ($row['expected_output_mode'] === 'server' && $row['expected_output'])
                            {{ $row['expected_output'] }}
                        @elseif ($row['expected_output_mode'] === 'browser')
                            <span
                                class="js-expected-output muted"
                                data-locale="{{ $row['locale'] }}"
                                data-style="{{ strtolower($row['label']) }}"
                                data-sample="{{ $row['sample'] }}"
                                @if ($row['currency']) data-currency="{{ $row['currency'] }}" @endif
                            >browser check pending</span>
                        @else
                            <span class="muted">not compared</span>
                        @endif
                    </td>
                    <td class="{{ $row['status'] === 'fail' ? 'fail' : 'warning' }}">{{ $row['native_formatter']['output'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="panel">
        <h2>Formatter Styles</h2>
        <p class="muted">Full matrix. Red rows failed. Orange rows formatted using the wrong resolved locale.</p>
        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Locale</th>
                <th>Style</th>
                <th>Sample</th>
                <th>Currency</th>
                <th>Should resolve to</th>
                <th>Should output</th>
                <th>Valid locale</th>
                <th>Actual locale</th>
                <th>Separators</th>
                <th>Native `NumberFormatter`</th>
                <th>Laravel helper</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($styleRows as $row)
                <tr class="{{ $row['status'] === 'ok' ? '' : 'row-'.$row['status'] }}">
                    <td><code>{{ $row['locale'] }}</code></td>
                    <td><code>{{ $row['label'] }}</code></td>
                    <td><code>{{ $row['sample'] }}</code></td>
                    <td>
                        @if ($row['currency'])
                            <code>{{ $row['currency'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td><code>{{ $row['expected_locale'] }}</code></td>
                    <td class="expected-output">
                        @if ($row['expected_output_mode'] === 'server' && $row['expected_output'])
                            {{ $row['expected_output'] }}
                        @elseif ($row['expected_output_mode'] === 'browser')
                            <span
                                class="js-expected-output muted"
                                data-locale="{{ $row['locale'] }}"
                                data-style="{{ strtolower($row['label']) }}"
                                data-sample="{{ $row['sample'] }}"
                                @if ($row['currency']) data-currency="{{ $row['currency'] }}" @endif
                            >browser check pending</span>
                        @else
                            <span class="muted">not compared</span>
                        @endif
                    </td>
                    <td>
                        @if ($row['native_formatter']['valid_locale'])
                            <code>{{ $row['native_formatter']['valid_locale'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($row['native_formatter']['actual_locale'])
                            <code>{{ $row['native_formatter']['actual_locale'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($row['native_formatter']['decimal_symbol'] !== null)
                            <code>{{ $row['native_formatter']['decimal_symbol'] }}</code> /
                            <code>{{ $row['native_formatter']['grouping_symbol'] }}</code>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td class="{{ $row['status'] === 'fail' ? 'fail' : ($row['status'] === 'warning' ? 'warning' : 'ok') }}">{{ $row['native_formatter']['output'] }}</td>
                    <td>
                        @if ($row['laravel_number'])
                            <span class="{{ $row['laravel_number']['ok'] ? 'ok' : 'fail' }}">{{ $row['laravel_number']['output'] }}</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
</main>
<script>
    const expectedOutputElements = document.querySelectorAll('.js-expected-output');

    for (const element of expectedOutputElements) {
        const locale = element.dataset.locale;
        const style = element.dataset.style;
        const sample = Number(element.dataset.sample);
        const currency = element.dataset.currency;

        try {
            let formatterOptions = {};

            if (style === 'currency' && currency) {
                formatterOptions = { style: 'currency', currency };
            } else if (style === 'scientific') {
                formatterOptions = { notation: 'scientific' };
            } else {
                element.textContent = 'not compared';
                element.className = 'muted';
                continue;
            }

            const output = new Intl.NumberFormat(locale.replace('_', '-'), formatterOptions).format(sample);

            element.textContent = output;
            element.className = '';
        } catch (error) {
            element.textContent = 'browser format failed';
            element.className = 'warning';
        }
    }
</script>
</body>
</html>
