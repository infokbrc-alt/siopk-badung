<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    public function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if (isset($uses[RefreshDatabase::class])) {
            $this->afterApplicationCreated(function () {
                if (DB::connection()->getDriverName() === 'mysql') {
                    $tables = DB::select('SHOW TABLES');
                    foreach ($tables as $table) {
                        $tableName = reset($table);
                        DB::statement("ALTER TABLE `{$tableName}` AUTO_INCREMENT = 1");
                    }
                }
            });
        }

        return $uses;
    }
}
