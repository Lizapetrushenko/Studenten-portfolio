<?php

namespace App\Models;

/** @deprecated Use User with role=student; kept as a compatibility alias. */
class Student extends User
{
    protected $table = 'users';
}
