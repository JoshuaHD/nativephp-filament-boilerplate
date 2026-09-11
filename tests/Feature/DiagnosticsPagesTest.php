<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can view the php info page', function () {
    $response = $this
        ->actingAs(User::factory()->create(), 'web')
        ->get(route('diagnostics.php-info'));

    $response
        ->assertSuccessful()
        ->assertSeeText('PHP Info')
        ->assertSeeText('Extension Checks');
});

test('authenticated users can view the icu test page', function () {
    $response = $this
        ->actingAs(User::factory()->create(), 'web')
        ->get(route('diagnostics.icu-probe'));

    $response
        ->assertSuccessful()
        ->assertSeeText('ICU Test')
        ->assertSeeText('Capability Checks');
});

test('authenticated users can view the icu android report', function () {
    $response = $this
        ->actingAs(User::factory()->create(), 'web')
        ->get(route('diagnostics.icu-check'));

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'summary' => ['status', 'likely_source', 'minimal_repro', 'recommended_issue_title'],
            'runtime' => ['php_sapi', 'php_version', 'laravel_version', 'nativephp_mobile_version'],
            'intl' => ['extension_loaded', 'classes', 'functions'],
            'repro_attempts',
        ]);
});
