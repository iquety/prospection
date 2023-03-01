<?php

declare(strict_types=1);

namespace Tests\Materialization\Mysql;

class MysqlMaterializerTest extends MysqlMaterializerTestCase
{
    /** @test */
    public function methodName(): void
    {
        $this->makeEvents();

        $this->assertTrue(true);
    }

    // /** @test */
    // public function aggregateMaterialize(): void
    // {
    //     $materialize = $this->materializerFactory();
    //     $materialize->materialize($streamEntity, $this->materializationFactory());
    // }
}
