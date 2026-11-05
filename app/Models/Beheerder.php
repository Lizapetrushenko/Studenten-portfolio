<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beheerder extends Model
{
    protected $table = 'administrators';
    protected $fillable = ['naam'];
}
