<?php

namespace App\Models;

/** @deprecated Use User with role=admin; kept as a compatibility alias. */
class Beheerder extends User
{
    protected $table = 'users';
}
