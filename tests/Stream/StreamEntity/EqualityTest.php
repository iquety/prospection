<?php

declare(strict_types=1);

namespace Tests\Stream\StreamEntity;

use Iquety\Domain\Core\IdentityObject;
use Tests\Stream\Support\DummyStreamEntity;
use Tests\Stream\Support\DummyStreamEntityOtherLabel;
use Tests\Stream\Support\DummyStreamEntitySameLabel;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class EqualityTest extends TestCase
{
    /** @test */
    public function sameObjectEqualIdentity(): void
    {
        $one = DummyStreamEntity::factory($this->stateValues());
        $two = DummyStreamEntity::factory($this->stateValues());

        $this->assertTrue($one->equalTo($two));
    }

    /** @test */
    public function sameObjectDiffIdentity(): void
    {
        $one = DummyStreamEntity::factory($this->stateValues());

        $values = $this->stateValues();
        $values['aggregateId'] = new IdentityObject('56789');
        $two = DummyStreamEntity::factory($values);

        $this->assertFalse($one->equalTo($two));
    }

    /** @test */
    public function equalIdentityEqualLabel(): void
    {
        $one = DummyStreamEntity::factory($this->stateValues());
        $two = DummyStreamEntitySameLabel::factory($this->stateValues());

        $this->assertTrue($one->equalTo($two));
    }

    /** @test */
    public function equalIdentityDifferentLabel(): void
    {
        $one = DummyStreamEntity::factory($this->stateValues());
        $two = DummyStreamEntityOtherLabel::factory($this->stateValues());

        $this->assertFalse($one->equalTo($two));
    }
}
