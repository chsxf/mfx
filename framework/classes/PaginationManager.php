<?php
/**
 * Pagination management class
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

use CheeseBurgames\MFX\DataValidator\FieldType;

/**
 * Helper class for managing pagination
 */
final class PaginationManager
{
	/**
	 * @var IPaginationProvider Reference to the pagination information provider
	 */
	private $_provider;
	
	/**
	 * @var int Total number of items
	 */
	private $_totalItemCount;
	/**
	 * @var int Index of the first element of the page
	 */		
	private $_currentPageStart;
	/**
	 * @var int Number of items per page
	 */
	private $_pageCount;
	/**
	 * @var array Extra parameters for pagination requests
	 */		
	private $_extraParameters;
	
	/**
	 * Constructor
	 * @param IPaginationProvider $provider Pagination information provider
	 * @param array $extraParams Extra parameter keys for further requests
	 */
	public function __construct(IPaginationProvider $provider, array $extraParameterKeys = array()) {
		$this->_provider = $provider;
		$this->_totalItemCount = $this->_provider->totalItemCount();
		
		$this->_extraParameters = array();
		foreach ($extraParameterKeys as $k)
			$this->_extraParameters[$k] = NULL;
		
		$this->_buildFromRequest();
	}
	
	/**
	 * Builds pagination information from request
	 */
	private function _buildFromRequest() {
		if (isset($_REQUEST['page_count']))
			$reqCount = intval($_REQUEST['page_count']);
		else
			$reqCount = $this->_provider->defaultPageCount();
		$this->_pageCount = $reqCount;
		
		if (isset($_REQUEST['page_start']))
			$reqStart = intval($_REQUEST['page_start']);
		else
			$reqStart = 0;
		if ($reqStart > $this->_totalItemCount)
			$reqStart = max(0, $this->_totalItemCount - $this->_pageCount);
		$this->_currentPageStart = max(0, $reqStart);
		
		foreach (array_keys($this->_extraParameters) as $k) {
			if (isset($_REQUEST[$k]))
				$this->_extraParameters[$k] = $_REQUEST[$k];
		}
	}
	
	/**
	 * Sets a registered extra parameter's value
	 * @param string $key Extra parameter's key
	 * @param mixed $value A scalar value
	 */
	public function setExtraParameter($key, $value) {
		if (array_key_exists($key, $this->_extraParameters))
			$this->_extraParameters[$key] = $value;
	}
	
	/**
	 * Generates a SQL LIMIT clause based on current page information
	 * @return string
	 */
	public function sqlLimit() {
		return sprintf(" LIMIT %d, %d", $this->_currentPageStart, $this->_pageCount);
	}
	
	/**
	 * Tells how many pages exists
	 * @return int
	 */
	public function getPagesCount() {
		return ($this->_pageCount == 0) ? 1 : max(1, ceil($this->_totalItemCount / $this->_pageCount));
	}
	
	/**
	 * Tells how many items should be displayed per page
	 * @return number
	 */
	public function getItemCountPerPage() {
		return $this->_pageCount;
	}
	
	/**
	 * Computes the 0-based current page index
	 * @return int
	 */
	public function getCurrentPageIndex() {
		return ($this->_pageCount == 0) ? 0 : floor($this->_currentPageStart / $this->_pageCount);
	}
	
	/**
	 * Tells the total count of items
	 * @return int
	 */
	public function getTotalItemCount() {
		return $this->_totalItemCount;
	}

	/**
	 * Tells the start index for the current page
	 * @return int
	 */
	public function getCurrentPageStart() {
		return $this->_currentPageStart;
	}

	/**
	 * Gets the URL parameters for the current page
	 * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
	 * @return string
	 */
	public function getCurrentPageURLParams($includeExtraParameters = true) {
		return $this->pageURLParams($this->getCurrentPageIndex(), $includeExtraParameters);
	}
	
	/**
	 * Tells if a previous page exists
	 * @return boolean
	 */
	public function hasPrevPage() {
		return ($this->_currentPageStart > 0);
	}
	
	/**
	 * Gets the previous page start index
	 * @return int
	 */
	public function prevPageStart() {
		return max(0, $this->_currentPageStart - $this->_pageCount);
	}
	
	/**
	 * Gets the previous page URL parameters
	 * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
	 * @return string
	 */
	public function prevPageURLParams($includeExtraParameters = true) {
		$args = array(
				'page_start' => $this->prevPageStart(),
				'page_count' => $this->_pageCount
		);
		if ($includeExtraParameters)
			$args = array_merge($this->_extraParameters, $args);
		return http_build_query($args);
	}
	
	/**
	 * Tells if a next page exists
	 * @return boolean
	 */
	public function hasNextPage() {
		return ($this->_currentPageStart < $this->_totalItemCount - $this->_pageCount);
	}
	
	/**
	 * Gets the next page start index
	 * @return int
	 */
	public function nextPageStart() {
		return max(0, min($this->_totalItemCount - 1, $this->_currentPageStart + $this->_pageCount));
	}
	
	/**
	 * Gets the next page URL parameters
	 * @param boolean $includeExtraParameters If set, includes the extra parameters in the URL params
	 * @return string
	 */
	public function nextPageURLParams($includeExtraParameters = true) {
		$args = array(
				'page_start' => $this->nextPageStart(),
				'page_count' => $this->_pageCount
		);
		if ($includeExtraParameters)
			$args = array_merge($this->_extraParameters, $args);
		return http_build_query($args);
	}
	
	/**
	 * Gets the start index of the specified page
	 * @param int $pageIndex Page index
	 * @return int
	 */
	public function pageStart($pageIndex) {
		$pageIndex = max(0, intval($pageIndex));
		return min($this->_totalItemCount - 1, $pageIndex * $this->_pageCount);
	}
	
	/**
	 * Get the URL parameters of the specified page
	 * @param int $pageIndex Page index
	 * @return string
	 */
	public function pageURLParams($pageIndex, $includeExtraParameters = true) {
		$args = array(
				'page_start' => $this->pageStart($pageIndex),
				'page_count' => $this->_pageCount
		);
		if ($includeExtraParameters)
			$args = array_merge($this->_extraParameters, $args);
		return http_build_query($args);
	}
	
	/**
	 * Adds current page parameters to the specified DataValidator instance
	 * @param DataValidator $validator
	 */
	public function addCurrentPageDataValidatorFields(DataValidator $validator) {
		$validator->createField('page_start', new FieldType(FieldType::POSITIVEZERO_INTEGER), $this->pageStart($this->getCurrentPageIndex()), false);
		$validator->createField('page_count', new FieldType(FieldType::POSITIVEZERO_INTEGER), $this->_pageCount, false);
	}
}