<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('Settings');

test('System Administrators can access file manager', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/elfinder');
    $response->assertSuccessful();
});

test('Guest cannot access file manager', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/elfinder');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others roles except System Administrators cannot access file manager', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/elfinder');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
