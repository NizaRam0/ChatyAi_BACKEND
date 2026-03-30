<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('users can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $tokenId = explode('|', $token)[0];

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response->assertNoContent();

    expect(PersonalAccessToken::find($tokenId))->toBeNull();
});
