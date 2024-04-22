<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

uses(DatabaseTransactions::class);
uses()->group('RBAC');

test('System Administrators can access list of roles', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/role');
    $response->assertSuccessful()->assertSeeText('Roles');
    $response->assertSeeText('Add Role');
    $response->assertSeeTextInOrder(['Name', 'Users', 'Actions']);
});

test('System Administrators can access role create form', function () {
    $adminUser = createTestUser();

    $response = $this->actingAs($adminUser)->get('/role/create');
    $response->assertSeeText([
        'Roles',
        'Add Role',
        'Name',
        'Navigations',
        'Permissions',
        'RBAC',
        'Application Settings',
    ])->assertSuccessful();
});

test('System Administrators can create role', function () {
    $adminUser = createTestUser();

    $this->actingAs($adminUser)->post('/role', [
        'name' => 'Test Role',
    ])->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'name' => 'Test Role',
    ]);
});

test(
    'System Administrators receive validation error when the validation check is not passed during create',
    function () {
        $adminUser = createTestUser();

        $this->actingAs($adminUser)->post('/role', [
            'name' => null,
        ])->assertSessionHasErrors([
            'name' => 'The name field is required.',
        ]);
    }
);

test('System Administrators can access role edit form', function () {
    $adminUser = createTestUser();
    $role = Role::create([
        'name' => 'Test Role',
    ]);

    $response = $this->actingAs($adminUser)->get('/role/'.$role->id.'/edit');
    $response->assertSeeText([
        'Roles',
        'Edit Role',
        'Name',
        'Navigations',
        'Permissions',
        'RBAC',
        'Application Settings',
    ])->assertSuccessful();
});

test('System Administrators can update role', function () {
    $adminUser = createTestUser();

    $role = Role::create([
        'name' => 'Test Role',
    ]);

    $this->actingAs($adminUser)->put('/role/'.$role->id, [
        'id' => $role->id,
        'name' => 'Test Role Updated',
    ])->assertRedirect();

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'Test Role Updated',
    ]);
});

test(
    'System Administrators receive validation error when the validation check is not passed during update',
    function () {
        $adminUser = createTestUser();

        $role = Role::create([
            'name' => 'Test Role',
        ]);

        $this->actingAs($adminUser)->put('/role/'.$role->id, [
            'id' => $role->id,
            'name' => null,
        ])->assertSessionHasErrors([
            'name' => 'The name field is required.',
        ]);
    }
);

test('System Administrators can delete role', function () {
    $adminUser = createTestUser();

    $role = Role::create([
        'name' => 'Test Role',
    ]);

    $this->actingAs($adminUser)->delete('/role/'.$role->id)->assertStatus(Response::HTTP_OK);

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('Guest cannot access roles', function () {
    $user = createTestUser('');

    $response = $this->actingAs($user)->get('/role');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(Response::HTTP_FORBIDDEN);
});

test('User with others roles except System Administrators cannot access roles', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/role');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }
});
