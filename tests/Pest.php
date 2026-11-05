<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(DatabaseTransactions::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Test Helper Functions
|--------------------------------------------------------------------------
|
| Common helpers for seeding roles, test users, and regions across tests.
|
*/

function createAdminUser(string $email = 'admin-test@cirebon-rtlh.test', string $name = 'Admin Test'): \App\Models\User
{
    $adminRole = \App\Models\Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $user = \App\Models\User::firstOrCreate(
        ['email' => $email],
        ['name' => $name, 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (! $user->hasRole('admin')) {
        $user->roles()->attach($adminRole);
    }
    return $user;
}

function createSurveyorUser(string $email = 'surveyor-test@cirebon-rtlh.test', string $name = 'Surveyor Test'): \App\Models\User
{
    $surveyorRole = \App\Models\Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);
    $user = \App\Models\User::firstOrCreate(
        ['email' => $email],
        ['name' => $name, 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (! $user->hasRole('surveyor')) {
        $user->roles()->attach($surveyorRole);
    }
    return $user;
}

function getTestVillage(string $code = '32.74.01.1001', string $name = 'Kesenden'): \App\Models\Region
{
    return \App\Models\Region::firstOrCreate(
        ['code' => $code],
        ['name' => $name, 'type' => 'village', 'level' => 2, 'is_active' => true]
    );
}
