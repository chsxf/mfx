<?php

namespace chsxf\MFX;

/**
 * @since 1.0
 */
interface IDatabaseUpdater
{
    /**
     * Retrieves the key for this updater
     * @since 1.0
     * @return string
     */
    public function key(): string;

    /**
     * Retrieves the path to the SQL update file for this updater
     * @since 1.0
     * @return string
     */
    public function pathToSQLFile(): string;
}
