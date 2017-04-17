<?php
/**
 * Pagination provider interface
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

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