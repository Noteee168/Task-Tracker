<?php

use App\Models\User;

test('registration screen can be rendered during initial setup when no manager exists', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new manager can register during initial setup', function () {
    $response = $this->post('/register', [
        'name' => 'Pengelola Utama',
        'email' => 'pengelola@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration screen redirects to login when manager account already exists', function () {
    User::factory()->create();

    $response = $this->get('/register');

    $response->assertRedirect(route('login'));
});

test('cannot register new account when manager account already exists', function () {
    User::factory()->create();

    $response = $this->post('/register', [
        'name' => 'Pengguna Lain',
        'email' => 'lain@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    expect(User::count())->toBe(1);
});
