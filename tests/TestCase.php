<?php

namespace Tests;

use Database\Seeders\CoreReferenceDataSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('education_programs')) {
            $this->seed(CoreReferenceDataSeeder::class);
        }
    }
}
