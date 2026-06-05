<?php

test('the application redirects guests to the Filament login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
