<?php

use App\Models\Role;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

uses(DatabaseTransactions::class);
uses()->group('Settings');

test('System Administrators can access list of settings', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/setting');
    $response->assertSuccessful()->assertSeeText('Settings');
    $response->assertSeeTextInOrder(['Name', 'Value', 'Description', 'Actions']);
});

test('System Administrators cannot access settings create form', function () {
    $adminUser = createTestUser();
    $response = $this->actingAs($adminUser)->get('/setting/create');
    $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
});

test('System Administrators can access settings edit form', function () {
    $adminUser = createTestUser();
    $setting = Setting::where('key', 'system.base.log.web_access')->first();
    $response = $this->actingAs($adminUser)->get("/setting/$setting->id/edit");
    $response->assertSuccessful();
});

test('System Administrators can update settings', function () {
    $adminUser = createTestUser();
    $setting = Setting::where('key', 'system.base.log.web_access')->first();
    $this->actingAs($adminUser)->put("/setting/$setting->id", [
        'id' => $setting->id,
        'value' => '0',
    ]);

    $this->assertDatabaseHas('settings', [
        'id' => $setting->id,
        'value' => stringEncryption('encrypt', 0),
    ]);
});

test('Guest cannot access settings', function () {
    $user = createTestUser('');

    $response = $this->actingAs($user)->get('/setting');
    $response->assertSeeText([
        '403',
        'Forbidden',
    ])->assertStatus(403);
});

test('User with others except System Administrators and Tendik roles cannot access settings', function () {
    $roles = Role::whereNotIn('name', ['System Administrators', 'Tendik'])
        ->pluck('name')
        ->toArray();

    foreach ($roles as $role) {
        $user = createTestUser($role);
        $response = $this->actingAs($user)->get('/setting');
        $response->assertSeeText([
            '403',
            'Forbidden',
        ])->assertStatus(403);
    }
});
