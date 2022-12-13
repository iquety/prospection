<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Query;
use Tests\EventStore\EventStoreCase;

abstract class AbstractCase extends EventStoreCase
{
    use AbstractQueryCount;
    use AbstractQueryList;
    use AbstractQueryListDate;
    use AbstractQueryListVersion;
    use AbstractQueryListAggreg;
    use AbstractQueryListConsol;
    use AbstractQueryNext;

    abstract public function queryFactory(): Query;

    abstract public function resetDatabase(): void;
}
