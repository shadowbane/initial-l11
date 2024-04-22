<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('RBAC');

test('System Administrators can access list of permissions', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/permission');
    $response->assertSuccessful()->assertSeeText('Permissions');
    $response->assertDontSeeText('Add Permission');
    $response->assertSeeTextInOrder(['Shortname', 'Name']);
});

test('Guest cannot access permissions menu', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/permission');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others roles except System Administrators cannot access permission menu', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/permission');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
