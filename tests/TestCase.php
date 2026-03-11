<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml env vars don't override the static Env::$repository that
        // gets populated from .env on first boot. Force the app environment to
        // "testing" so that runningUnitTests() returns true and CSRF is bypassed.
        $this->app['env'] = 'testing';

        // Ensure the session uses the array driver, not the database driver,
        // so tests don't need the sessions table to exist.
        $this->app['config']->set('session.driver', 'array');
    }
}
