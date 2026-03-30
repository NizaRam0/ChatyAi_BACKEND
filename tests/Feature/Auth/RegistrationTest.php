<?php

use App\Models\User;

test('new users can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Strong#Pass123',
        'password_confirmation' => 'Strong#Pass123',
    ]);

    $response->assertNoContent();

    $this->assertDatabaseHas(User::class, [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});
