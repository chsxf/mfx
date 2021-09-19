<?php
/**
 * Pagination provider interface
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @version 1.0
 */

namespace chsxf\MFX;

/**
 * Interface describing objects that provide pagination information
 */
interface IPaginationProvider
{
	/**
	 * Retrieves the total number of items
	 * @return int
	 */
	public function totalItemCount();
	
	/**
	 * Retrieves the default number of items to display per page
	 * @return int
	 */
	public function defaultPageCount();
}