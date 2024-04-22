<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('Settings');

test('System Administrators can access error log manager', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/log');
    $response->assertSuccessful()->assertSeeText('Log Manager');
    $response->assertSeeTextInOrder(['File name', 'Date', 'Last modified', 'File size', 'Actions']);
});

test('Guest cannot access error log manager', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/log');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others roles except System Administrators cannot access error log manager', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/log');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
