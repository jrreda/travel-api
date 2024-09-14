<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login return tokens with valid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access_token']);
});

test('login return error with invalid credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'nonexisting@user.com',
        'password' => 'password',
    ]);

    $response->assertStatus(401);
});
