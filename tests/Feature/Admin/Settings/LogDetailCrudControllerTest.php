<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('Settings');

test('System Administrators can access log details', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/logdetail');
    $response->assertSuccessful()->assertSeeText('Log Details');
    $response->assertSeeTextInOrder(['Timestamps', 'Description', 'Affected Model', 'Causer']);
});

test('Guest cannot access log details', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/logdetail');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others roles except System Administrators cannot access log details', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/logdetail');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
