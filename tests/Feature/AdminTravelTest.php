<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('public user cannot add travel', function () {
    $response = $this->postJson('/api/v1/admin/travels');

    $response->assertStatus(401);
});

test('non admin user cannot add travel', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', 'editor')->value('id'));

    $response = $this->actingAs($user)
        ->postJson('/api/v1/admin/travels');

    $response->assertStatus(403);
});

test('saves travel successfully with valid data', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', 'admin')->value('id'));

    // faild to create a new travel (missing data)
    $response = $this->actingAs($user)
        ->postJson('/api/v1/admin/travels', [
            'name' => 'Test Travel',
        ]);
    $response->assertStatus(422);

    // create a new travel successfully
    $response = $this->actingAs($user)
        ->postJson('/api/v1/admin/travels', [
                'name'           => 'Test Travel',
                'description'    => 'This is a test travel.',
                'is_public'      => 1,
                'number_of_days' => 5,
            ]);
    $response->assertStatus(201);

    // access public Travel
    $response = $this->get('/api/v1/travels');
    $response->assertJsonFragment(['name' => 'Test Travel']);
});