<?php

use App\Models\MenuItem;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

uses(DatabaseTransactions::class);
uses()->group('RBAC');

test('System Administrators can access list of menu items', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/menu-item');
    $response->assertSuccessful()->assertSeeText('Side Menu');
    $response->assertSeeText('Add Side Menu');
    $response->assertSeeText('Reorder Side Menu');
    $response->assertSeeTextInOrder(['Name', 'Grouping', 'Parent', 'Actions']);
});

test('System Administrators can access menu item create form', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/menu-item/create');
    $response->assertSuccessful()->assertSeeText('Side Menu');
    $response->assertSeeText([
        'Add Side Menu',
        'Label',
        'Grouping',
        'Parent',
        'URL',
        'Icon',
        'Roles',
    ]);
});

test('System Administrators can create menu item', function () {
    $adminUser = createTestUser();

    $this->actingAs($adminUser)->post('/menu-item', [
        'name' => 'Test Menu Item',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
        'roles' => '["1"]',
    ])->assertStatus(Response::HTTP_FOUND);

    $menu = MenuItem::latest()->first();

    $this->assertDatabaseHas('menu_items', [
        'id' => $menu->id,
        'name' => 'Test Menu Item',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
    ]);
});

test(
    'System Administrators receive validation error when the validation check is not passed during create',
    function () {
        $adminUser = createTestUser();

        $this->actingAs($adminUser)->post('/menu-item', [
            'name' => null,
        ])->assertSessionHasErrors();
    }
);

test('System Administrators can access menu item edit form', function () {
    $adminUser = createTestUser();

    $response = $this->actingAs($adminUser)->get('/menu-item/1/edit');
    $response->assertSeeText([
        'Edit Side Menu',
        'Label',
        'Grouping',
        'Parent',
        'URL',
        'Icon',
        'Roles',
    ])->assertSuccessful();
});

test('System Administrators can update menu item', function () {
    $adminUser = createTestUser();

    $menuItem = MenuItem::create([
        'name' => 'Test Menu Item',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
        'roles' => '["1"]',
    ]);
    $this->actingAs($adminUser)->put("/menu-item/$menuItem->id", [
        'id' => $menuItem->id,
        'name' => 'Test Menu Item - edited',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
        'roles' => '["1"]',
    ])->assertStatus(Response::HTTP_FOUND);

    $this->assertDatabaseHas('menu_items', [
        'id' => $menuItem->id,
        'name' => 'Test Menu Item - edited',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
    ]);
});
test(
    'System Administrators receive validation error when the validation check is not passed during update',
    function () {
        $adminUser = createTestUser();


        $menuItem = MenuItem::create([
            'name' => 'Test Menu Item',
            'grouping' => 'Test Grouping',
            'parent_id' => 1,
            'link' => '/test',
            'icon' => 'la-address-book',
            'roles' => '["1"]',
        ]);
        $this->actingAs($adminUser)->put("/menu-item/$menuItem->id", [
            'id' => $menuItem->id,
            'name' => null,
            'parent_id' => 'abc',
        ])->assertSessionHasErrors(['name', 'parent_id']);
    }
);

test('System Administrators can delete menu item', function () {
    $adminUser = createTestUser();

    $menuItem = MenuItem::create([
        'name' => 'Test Menu Item',
        'grouping' => 'Test Grouping',
        'parent_id' => 1,
        'link' => '/test',
        'icon' => 'la-address-book',
        'roles' => '["1"]',
    ]);
    $this->actingAs($adminUser)->delete("/menu-item/$menuItem->id")->assertStatus(Response::HTTP_OK);

    $this->assertDatabaseMissing('menu_items', [
        'id' => $menuItem->id,
    ]);
});

test('Guest cannot access menu items', function () {
    $user = createTestUser('');

    $this->actingAs($user)->get('/menu-item')
        ->assertStatus(Response::HTTP_FORBIDDEN)
        ->assertSeeText([
            '403',
            'Forbidden',
        ]);
});

test('User with others roles except System Administrators cannot access menu items', function () {
    $roles = Role::whereNotIn('name', ['System Administrators'])->pluck('name')->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/backup');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }
});
