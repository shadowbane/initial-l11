<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('Settings');

test('System Administrators can access backups', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/backup');
    $response->assertSuccessful()->assertSeeText('Backups');
    $response->assertSeeText('Create a new backup');
    $response->assertSeeTextInOrder(['Location', 'Date', 'File size', 'Actions']);
});

test('Guest cannot access backups', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/backup');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others roles except System Administrators cannot access backups', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/backup');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
