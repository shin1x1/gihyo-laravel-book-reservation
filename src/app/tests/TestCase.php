<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * @var bool
     */
    protected static $databaseMigrated = false;

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__ . '/../../bootstrap/start.php';
    }

    /**
     * migrate Database
     */
    protected function migrateDatabase()
    {
        if (static::$databaseMigrated) {
            return;
        }

        Artisan::call('migrate');
        $this->seed();

        static::$databaseMigrated = true;
    }
}
