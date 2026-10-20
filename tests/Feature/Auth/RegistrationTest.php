<?php

test('public registration screen is disabled for security', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
});

test('public registration endpoint rejects submissions', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(404);
    $this->assertGuest();
});
