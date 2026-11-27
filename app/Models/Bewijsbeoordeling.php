<?php

namespace App\Models;

/** @deprecated Reviews are stored on Evidence; use Evidence directly. */
class Bewijsbeoordeling extends Evidence
{
    protected $table = 'evidence';
}
