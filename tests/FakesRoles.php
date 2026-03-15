<?php

namespace Lwekuiper\StatamicConnect\Tests;

use Statamic\Facades\User;

trait FakesRoles
{
    protected function createAdmin()
    {
        return User::make()
            ->makeSuper()
            ->email('admin@example.com')
            ->save();
    }

    protected function createUser()
    {
        return User::make()
            ->email('user@example.com')
            ->save();
    }
}
