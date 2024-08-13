<?php

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\DatabaseConnectionInstance;
use chsxf\MFX\Services\IDatabaseService;

/**
 * @since 2.0
 * @ignore
 */
final class DatabaseServiceProxy implements IDatabaseService
{
    public function __construct(private readonly IDatabaseService $databaseService)
    {
    }

    public function open(string $server = self::DEFAULT_CONNECTION, bool $forceNew = false): DatabaseConnectionInstance
    {
        return $this->databaseService->open($server, $forceNew);
    }

    public function close(DatabaseConnectionInstance &$connectionInstance)
    {
        $this->databaseService->close($connectionInstance);
    }
}
