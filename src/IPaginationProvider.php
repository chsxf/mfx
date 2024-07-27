<?php

namespace chsxf\MFX;

/**
 * Interface describing objects that provide pagination information
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
interface IPaginationProvider
{
    /**
     * Retrieves the total number of items
     * @since 1.0
     * @return int
     */
    public function totalItemCount(): int;

    /**
     * Retrieves the default number of items to display per page
     * @since 1.0
     * @return int
     */
    public function defaultPageCount(): int;
}
