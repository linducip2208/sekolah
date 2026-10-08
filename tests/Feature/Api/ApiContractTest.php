<?php

// API v1 contract: root discovery, health, and JSON-only error envelope.
// Guards the Flutter mobile contract: no HTML error pages on /api/*,
// even when the client omits `Accept: application/json`.

test('api v1 root returns discovery document (200)', function () {
    $this->getJson('/api/v1')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'name' => 'eSchool API',
            'version' => 'v1',
            'status' => 'ok',
        ]);
});

test('api v1 health is ok (200)', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(['status' => 'ok'])
        ->assertJsonStructure(['status', 'time']);
});

test('protected endpoint without token returns JSON 401 (no Accept header)', function () {
    // Raw call without getJson(): simulates clients that do not send
    // `Accept: application/json`. Must still be JSON, never HTML.
    $response = $this->get('/api/v1/auth/me');

    $response->assertUnauthorized();
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

test('unknown api route returns JSON 404 (no Accept header)', function () {
    $response = $this->get('/api/v1/route-yang-tidak-ada');

    $response->assertNotFound();
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

test('login with wrong credentials returns JSON 401', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'tidak@ada.test',
        'password' => 'salah-salah',
        'device_name' => 'test',
    ])->assertUnauthorized();
});

test('login without fields returns JSON 422 with errors bag', function () {
    $this->postJson('/api/v1/auth/login', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

test('api responses carry request id correlation header', function () {
    $this->getJson('/api/v1/health')->assertHeader('X-Request-ID');
});
