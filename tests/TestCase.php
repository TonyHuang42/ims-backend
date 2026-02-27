<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property \App\Models\Role|null $adminRole
 * @property \App\Models\User|null $admin
 * @property string|null $adminToken
 * @property \App\Models\User|null $user
 * @property string|null $userToken
 */
abstract class TestCase extends BaseTestCase
{
    //
}
