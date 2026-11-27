<?php

namespace App\Models;

/** @deprecated Use User with role=teacher; kept as a compatibility alias. */
class Docent extends User
{
    protected $table = 'users';
}
