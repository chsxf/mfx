<?php
/**
 * SubrouteType enum
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Enum used to identify sub-routes type for request responses
 */
enum SubRouteType: int
{
	case VIEW = 1;
	case REDIRECT = 2;
	case JSON = 3;
	case XML = 4;
	case STATUS = 5;
}
