<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);
uses()->group('RBAC');

test('System Administrators can access list of user', function () {
    $user = createTestUser();
    $response = $this->actingAs($user)->get('/user');
    $response->assertStatus(200)->assertSeeText('Users');
    $response->assertSeeTextInOrder(['Name', 'Email', 'Roles', 'Actions']);
});

test('System Administrator can access edit user form', function () {
    $user = createTestUser();
    $response = $this->actingAs($user)->get('/user/'.$user->id.'/edit');
    $response->assertSeeText('Edit user');

    $response->assertSeeText('Name');
    $response->assertSeeText('Email');
    $response->assertSeeText('Credential');
    $response->assertSeeText('Settings');
    $response->assertSeeText('ACL');

    $response->assertStatus(200);
});

test('System Administrator can perform update data user', function () {
    $user = createTestUser();
    $response = $this->actingAs($user)->put('/user/'.$user->id, [
        'id' => $user->id,
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'tes@example.com',
        'whatsapp' => '081234567890',
    ]);
    $response->assertStatus(302);
});

test('System Administrator receive validation error when the validation check is not passed', function () {
    $user = createTestUser();
    $response = $this->actingAs($user)->put('/user/'.$user->id, [
        'id' => $user->id,
        'name' => null,
        'email' => null,
    ]);
    $response->assertSessionHasErrors(['name']);
});

test('Guest cant access user except System Administrator', function () {
    $user = createTestUser('');
    $response = $this->actingAs($user)->get('/user');
    $response->assertStatus(403);
});

test('User with others roles except System Administrators cannot access user', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/user');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
