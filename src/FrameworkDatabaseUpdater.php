<?php

declare(strict_types=1);

namespace chsxf\MFX;

/**
 * @ignore
 */
final class FrameworkDatabaseUpdater implements IDatabaseUpdater
{
    /**
     * {@inheritDoc}
     * @see \chsxf\MFX\DatabaseUpdater::key()
     */
    public function key(): string
    {
        return 'mfx';
    }

    /**
     * {@inheritDoc}
     * @see \chsxf\MFX\DatabaseUpdater::pathToSQLFile()
     */
    public function pathToSQLFile(): string
    {
        return ROOT . '/sql/mfx.sql';
    }
}
