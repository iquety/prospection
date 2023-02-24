<?php

declare(strict_types=1);

namespace Iquety\Prospection\Materialization;

use DateTimeImmutable;
use Iquety\Prospection\Stream\StreamEntity;

interface Materializer
{
    public function materialize(StreamEntity $aggregate, Materialization $materializacao): void;

    public function materializeSince(
        StreamEntity $aggregate,
        Materialization $materialization,
        DateTimeImmutable $startDate
    ): void;

    public function materializeUpdatesSince(
        StreamEntity $aggregate,
        Materialization $materialization,
        DateTimeImmutable $startDate
    ): void;
}
