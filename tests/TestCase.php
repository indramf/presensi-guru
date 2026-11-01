<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.checkpoint_path' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'audit-cp-test-'.getmypid().'.json']);
    }
}
