<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Role|null $adminRole
 * @property Role|null $userRole
 * @property User|null $admin
 * @property string|null $adminToken
 * @property User|null $user
 * @property string|null $userToken
 */
abstract class TestCase extends BaseTestCase
{
    //
}
