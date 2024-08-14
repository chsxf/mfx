<?php

declare(strict_types=1);

namespace chsxf\MFX;

use chsxf\MFX\DataValidator\FieldType;

/**
 * Helper class for managing pagination
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 1.0
 */
final class PaginationManager
{
    /**
     * @var IPaginationProvider Reference to the pagination information provider
     */
    private IPaginationProvider $provider;

    /**
     * @var int Total number of items
     */
    private int $totalItemCount;
    /**
     * @var int Index of the first element of the page
     */
    private int $currentPageStart;
    /**
     * @var int Number of items per page
     */
    private int $pageCount;
    /**
     * @var array Extra parameters for pagination requests
     */
    private array $extraParameters;

    /**
     * Constructor
     * @since 1.0
     * @param IPaginationProvider $provider Pagination information provider
     * @param array $extraParams Extra parameter keys for further requests
     */
    public function __construct(IPaginationProvider $provider, array $extraParameterKeys = array())
    {
        $this->provider = $provider;
        $this->totalItemCount = $this->provider->totalItemCount();

        $this->extraParameters = array();
        foreach ($extraParameterKeys as $k) {
            $this->extraParameters[$k] = null;
        }

        $this->buildFromRequest();
    }

    /**
     * Builds pagination information from request
     */
    private function buildFromRequest()
    {
        if (isset($_REQUEST['page_count'])) {
            $reqCount = intval($_REQUEST['page_count']);
        } else {
            $reqCount = $this->provider->defaultPageCount();
        }
        $this->pageCount = $reqCount;

        if (isset($_REQUEST['page_start'])) {
            $reqStart = intval($_REQUEST['page_start']);
        } else {
            $reqStart = 0;
        }
        if ($reqStart > $this->totalItemCount) {
            $reqStart = max(0, $this->totalItemCount - $this->pageCount);
        }
        $this->currentPageStart = max(0, $reqStart);

        foreach (array_keys($this->extraParameters) as $k) {
            if (isset($_REQUEST[$k])) {
                $this->extraParameters[$k] = $_REQUEST[$k];
            }
        }
    }

    /**
     * Sets a registered extra parameter's value
     * @since 1.0
     * @param string $key Extra parameter's key
     * @param mixed $value A scalar value
     */
    public function setExtraParameter(string $key, mixed $value)
    {
        if (array_key_exists($key, $this->extraParameters)) {
            $this->extraParameters[$key] = $value;
        }
    }

    /**
     * Generates a SQL LIMIT clause based on current page information
     * @since 1.0
     * @return string
     */
    public function sqlLimit(): string
    {
        return sprintf(" LIMIT %d, %d", $this->currentPageStart, $this->pageCount);
    }

    /**
     * Tells how many pages exists
     * @since 1.0
     * @return int
     */
    public function getPagesCount(): int
    {
        return ($this->pageCount == 0) ? 1 : max(1, ceil($this->totalItemCount / $this->pageCount));
    }

    /**
     * Tells how many items should be displayed per page
     * @since 1.0
     * @return int
     */
    public function getItemCountPerPage(): int
    {
        return $this->pageCount;
    }

    /**
     * Computes the 0-based current page index
     * @since 1.0
     * @return int
     */
    public function getCurrentPageIndex(): int
    {
        return ($this->pageCount == 0) ? 0 : floor($this->currentPageStart / $this->pageCount);
    }

    /**
     * Tells the total count of items
     * @since 1.0
     * @return int
     */
    public function getTotalItemCount(): int
    {
        return $this->totalItemCount;
    }

    /**
     * Tells the start index for the current page
     * @since 1.0
     * @return int
     */
    public function getCurrentPageStart(): int
    {
        return $this->currentPageStart;
    }

    /**
     * Gets the URL parameters for the current page
     * @since 1.0
     * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
     * @return string
     */
    public function getCurrentPageURLParams(bool $includeExtraParameters = true): string
    {
        return $this->pageURLParams($this->getCurrentPageIndex(), $includeExtraParameters);
    }

    /**
     * Tells if a previous page exists
     * @since 1.0
     * @return boolean
     */
    public function hasPrevPage(): bool
    {
        return ($this->currentPageStart > 0);
    }

    /**
     * Gets the previous page start index
     * @since 1.0
     * @return int
     */
    public function prevPageStart(): int
    {
        return max(0, $this->currentPageStart - $this->pageCount);
    }

    /**
     * Gets the previous page URL parameters
     * @since 1.0
     * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
     * @return string
     */
    public function prevPageURLParams(bool $includeExtraParameters = true): string
    {
        $args = array(
            'page_start' => $this->prevPageStart(),
            'page_count' => $this->pageCount
        );
        if ($includeExtraParameters) {
            $args = array_merge($this->extraParameters, $args);
        }
        return http_build_query($args);
    }

    /**
     * Tells if a next page exists
     * @since 1.0
     * @return boolean
     */
    public function hasNextPage(): bool
    {
        return ($this->currentPageStart < $this->totalItemCount - $this->pageCount);
    }

    /**
     * Gets the next page start index
     * @since 1.0
     * @return int
     */
    public function nextPageStart(): int
    {
        return max(0, min($this->totalItemCount - 1, $this->currentPageStart + $this->pageCount));
    }

    /**
     * Gets the next page URL parameters
     * @since 1.0
     * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
     * @return string
     */
    public function nextPageURLParams(bool $includeExtraParameters = true): string
    {
        $args = array(
            'page_start' => $this->nextPageStart(),
            'page_count' => $this->pageCount
        );
        if ($includeExtraParameters) {
            $args = array_merge($this->extraParameters, $args);
        }
        return http_build_query($args);
    }

    /**
     * Gets the start index of the specified page
     * @since 1.0
     * @param int $pageIndex Page index
     * @return int
     */
    public function pageStart(int $pageIndex): int
    {
        $pageIndex = max(0, intval($pageIndex));
        return min($this->totalItemCount - 1, $pageIndex * $this->pageCount);
    }

    /**
     * Get the URL parameters of the specified page
     * @since 1.0
     * @param int $pageIndex Page index
     * @param bool $includeExtraParameters If set, includes the extra parameters in the URL params
     * @return string
     */
    public function pageURLParams(int $pageIndex, bool $includeExtraParameters = true): string
    {
        $args = array(
            'page_start' => $this->pageStart($pageIndex),
            'page_count' => $this->pageCount
        );
        if ($includeExtraParameters) {
            $args = array_merge($this->extraParameters, $args);
        }
        return http_build_query($args);
    }

    /**
     * Adds current page parameters to the specified DataValidator instance
     * @since 1.0
     * @param DataValidator $validator
     */
    public function addCurrentPageDataValidatorFields(DataValidator $validator)
    {
        $validator->createField('page_start', FieldType::POSITIVEZERO_INTEGER, $this->pageStart($this->getCurrentPageIndex()), false);
        $validator->createField('page_count', FieldType::POSITIVEZERO_INTEGER, $this->pageCount, false);
    }
}
