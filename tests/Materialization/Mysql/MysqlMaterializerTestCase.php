<?php

declare(strict_types=1);

namespace Tests\Materialization\Mysql;

use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Domain\Event\DomainEvent;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\Mysql\MysqlConnection;
use Iquety\Prospection\EventStore\Mysql\MysqlQuery;
use Iquety\Prospection\EventStore\Mysql\MysqlStore;
use Iquety\Prospection\Materialization\Mysql\MysqlMaterialization;
use Iquety\Prospection\Materialization\Mysql\MysqlMaterializer;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\EventStore\EventStoreCase;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityThr;
use Tests\EventStore\Support\DummyEntityTwo;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventThr;
use Tests\EventStore\Support\DummyEventTwo;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyStreamEntity;
use Tests\Stream\Support\DummyStreamEntityOtherLabel;
use Tests\TestCase;

class MysqlMaterializerTestCase extends EventStoreCase
{
    private static function connectionFactory(): MysqlConnection
    {
        return new MysqlConnection(
            'devel',
            'iquety-prospection-mysql',
            3306,
            'devel',
            'devel'
        );
    }

    public static function setUpBeforeClass(): void
    {
        $store = new MysqlStore(self::connectionFactory(), 'events');
        $store->removeTable();
        $store->createTable();
    }

    protected function eventStoreFactory(): EventStore
    {
        return new EventStore(
            new MysqlQuery(self::connectionFactory(), 'events'),
            new MysqlStore(self::connectionFactory(), 'events'),
            new JsonEventSerializer(),
        );
    }

    // public function entityOneFactory()
    // {
    //     return $this->streamEntityFactory(
    //         '2023-01-10 23:00:00',
    //         'UTC',
    //         DummyStreamEntity::class
    //     );
    // }

    // public function entityTwoFactory()
    // {
    //     return $this->streamEntityFactory(
    //         '2023-01-10 23:00:00',
    //         'UTC',
    //         DummyStreamEntityOtherLabel::class
    //     );
    // }

    // public function materializationFactory(): MysqlMaterialization
    // {
    //     $materialization = new MysqlMaterialization('table_materialized');

    //     $materialization->identity('id');
    //     $materialization->string('name');

    //     return $materialization;
    // }
    
    // public function materializerFactory(): MysqlMaterializer
    // {
    //     return new MysqlMaterializer($this->connectionFactory(), $this->queryFactory());
    // }

    // private function eventFactory(string $aggregateId, string $aggregateLabel, array $eventData): DomainEvent
    // {
    //     if (isset($eventData['identity']) === false) {
    //         $eventData['identity'] = new IdentityObject($aggregateId);
    //     }

    //     /** @var InvocationMocker */
    //     $event = $this->createMock(DomainEvent::class);
    //     $event->method('aggregateId')->willReturn(new IdentityObject($aggregateId));
    //     $event->method('aggregateLabel')->willReturn($aggregateLabel);
    //     $event->method('label')->willReturn('user.created');
    //     $event->method('toArray')->willReturn($eventData);

    //     /** @var DomainEvent $event */
    //     return $event;
    // }
    
    protected function randomWord(): string
    {
        $list = [
            'Sardinha',
            'Arroz',
            'Feijão',
            'Batata',
            'Cenoura',
            'Guaraná',
            'Coca Cola',
            'Cevada',
            'Couve',
            'Brócolis',
            'Frango',
        ];

        shuffle($list);

        return $list[0];
    }

    protected function dummyOneFactory(): DummyEventOne
    {
        return DummyEventOne::factory([
            'aggregateId' => new IdentityObject('123'),
            'one' => $this->randomWord()
        ]);
    }

    protected function dummyTwoFactory(): DummyEventTwo
    {
        return DummyEventTwo::factory([
            'aggregateId' => new IdentityObject('123'),
            'two' => $this->randomWord()
        ]);
    }

    protected function dummyThrFactory(): DummyEventThr
    {
        return DummyEventThr::factory([
            'aggregateId' => new IdentityObject('123'),
            'one' => $this->randomWord(),
            'two' => $this->randomWord()
        ]);
    }

    protected function makeEvents(): void
    {
        $eventStore = $this->eventStoreFactory();
        // $eventStore->registerEventType('user.created', function(array $state) {
        //     // ...
        // });

        $event = new EventSnapshot([
            'aggregateId' => new IdentityObject('123'),
            'one' => 'Maça',
            'two' => 'Pera',
            'thr' => 'Laranja'
        ]);

        $eventStore->store(DummyEntityOne::class, $event);
        $eventStore->store(DummyEntityTwo::class, $event);
        $eventStore->store(DummyEntityThr::class, $event);

        for($x=0; $x < 25; $x++) {
            $eventStore->store(DummyEntityOne::class, $this->dummyOneFactory());
            $eventStore->store(DummyEntityOne::class, $this->dummyTwoFactory());
    
            $eventStore->store(DummyEntityTwo::class, $this->dummyThrFactory());
            $eventStore->store(DummyEntityTwo::class, $this->dummyThrFactory());
        }
    }
}
