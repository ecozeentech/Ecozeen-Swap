<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every feature relies on the "user"/"admin"/"super-admin" roles
     * existing (e.g. registration assigns the "user" role), so seed them
     * for every test that refreshes the database.
     */
    protected $seed = true;

    protected $seeder = RolesAndPermissionsSeeder::class;
}
