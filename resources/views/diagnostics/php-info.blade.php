<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP Info</title>
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

        .meta,
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

        .card-ok {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .card-warning {
            background: #fff7ed;
            border-color: #fdba74;
        }

        .runtime-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 10px 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
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

        .ok {
            color: #166534;
        }

        .fail {
            color: #b91c1c;
        }

        .muted {
            color: #57534e;
        }

        code {
            font-family: ui-monospace, monospace;
            font-size: 13px;
            word-break: break-word;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px 4px;
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
        <h1>PHP Info</h1>
        <p>This page shows the current PHP runtime, loaded extensions, and a few capability checks that make packaging issues obvious.</p>

        <div class="cards">
            <div class="card card-ok">
                <strong>PHP version</strong>
                <div class="card-value">{{ $runtimeFingerprint['php_version'] }}</div>
            </div>
            <div class="card {{ in_array('intl', $loadedExtensions, true) ? 'card-ok' : 'card-warning' }}">
                <strong>`intl`</strong>
                <div class="card-value">{{ in_array('intl', $loadedExtensions, true) ? 'loaded' : 'missing' }}</div>
            </div>
            <div class="card {{ in_array('opcache', $loadedExtensions, true) ? 'card-ok' : 'card-warning' }}">
                <strong>`opcache`</strong>
                <div class="card-value">{{ in_array('opcache', $loadedExtensions, true) ? 'loaded' : 'missing' }}</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>Runtime Fingerprint</h2>
        <div class="runtime-grid">
            <div><strong>PHP SAPI</strong> <code>{{ $runtimeFingerprint['sapi'] }}</code></div>
            <div><strong>PHP binary</strong> <code>{{ $runtimeFingerprint['binary'] }}</code></div>
            <div><strong>OS family</strong> <code>{{ $runtimeFingerprint['os_family'] }}</code></div>
            <div><strong>Zend version</strong> <code>{{ $runtimeFingerprint['zend_version'] }}</code></div>
            <div><strong>Thread safe</strong> <code>{{ $runtimeFingerprint['thread_safe'] ? 'yes' : 'no' }}</code></div>
            <div><strong>Debug build</strong> <code>{{ $runtimeFingerprint['debug_build'] ? 'yes' : 'no' }}</code></div>
            <div><strong>Loaded php.ini</strong> <code>{{ $runtimeFingerprint['loaded_ini_file'] ?? 'none' }}</code></div>
            <div><strong>Host</strong> <code>{{ $runtimeFingerprint['host'] }}</code></div>
            <div><strong>Path</strong> <code>{{ $runtimeFingerprint['path'] }}</code></div>
            <div><strong>User agent</strong> <code>{{ $runtimeFingerprint['user_agent'] }}</code></div>
            <div style="grid-column: 1 / -1;"><strong>System</strong> <code>{{ $runtimeFingerprint['system'] }}</code></div>
        </div>
    </div>

    <div class="panel">
        <h2>Scanned INI Files</h2>
        @if ($runtimeFingerprint['scanned_ini_files'] !== [])
            <div class="runtime-grid">
                @foreach ($runtimeFingerprint['scanned_ini_files'] as $iniFile)
                    <div><code>{{ $iniFile }}</code></div>
                @endforeach
            </div>
        @else
            <p class="muted">No additional scanned INI files were reported.</p>
        @endif
    </div>

    <div class="panel">
        <h2>Extension Checks</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Version</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($extensionChecks as $row)
                    <tr>
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
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>Capability Checks</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($classChecks as $row)
                    <tr>
                        <td>class</td>
                        <td><code>{{ $row['name'] }}</code></td>
                        <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'available' : 'missing' }}</td>
                    </tr>
                @endforeach
                @foreach ($functionChecks as $row)
                    <tr>
                        <td>function</td>
                        <td><code>{{ $row['name'] }}</code></td>
                        <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'available' : 'missing' }}</td>
                    </tr>
                @endforeach
                @foreach ($constantChecks as $row)
                    <tr>
                        <td>constant</td>
                        <td><code>{{ $row['name'] }}</code></td>
                        <td class="{{ $row['available'] ? 'ok' : 'fail' }}">{{ $row['available'] ? 'defined' : 'missing' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>Loaded Extensions</h2>
        <p class="muted">This is the raw list from <code>get_loaded_extensions()</code>.</p>
        <code>{{ implode(', ', $loadedExtensions) }}</code>
    </div>
</main>
</body>
</html>
