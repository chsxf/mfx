<?php
/**
 * SubrouteType enum
 * 
 * @author Christophe SAUVEUR <christophe@cheeseburgames.com>
 * @version 1.0
 * @package framework
 */

namespace CheeseBurgames\MFX;

/**
 * Enum used to identify sub-routes type for request responses
 */
class SubRouteType extends Enum
{
	const __default = self::VIEW;
	
	const VIEW = 1;
	const EDIT = 2;
	const ASYNC_JSON = 3;
	const ASYNC_XML = 4;
}