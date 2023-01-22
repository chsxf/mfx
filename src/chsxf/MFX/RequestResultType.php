<?php

/**
 * RequestResultType enum
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Enum used to identify the type of request results
 */
enum RequestResultType: int
{
	case VIEW = 1;
	case REDIRECT = 2;
	case JSON = 3;
	case XML = 4;
	case STATUS = 5;
}
