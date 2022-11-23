<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\AggregateRoot;

class User extends AggregateRoot
{
    public function __construct(
        protected IdentityObject $aggregateId,
        protected string $name,
        protected string $email,
        protected string $status
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function status(): string
    {
        return $this->status;
    }

    public static function label(): string
    {
        return 'user';
    }
}