<?php

/**
 * Data validation field type enum class
 * 
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Enum of all available data validation types
 */
enum FieldType: string
{
		// Built-in HTML input types
	case CHECKBOX = 'checkbox';
	case COLOR = 'color';
	case DATE = 'date';
	case EMAIL = 'email';
	case FILE = 'file';
	case HIDDEN = 'hidden';
	case MONTH = 'month';
	case NUMBER = 'number';
	case RADIO = 'radio';
	case RANGE = 'range';
	case SELECT = 'select';
	case TEL = 'tel';
	case TEXT = 'text';
	case TIME = 'time';
	case URL = 'url';
	case WEEK = 'week';
	case PASSWORD = 'password';

		// Custom type
	case INTEGER = 'integer';
	case MULTI_SELECT = 'multiselect';
	case NEGATIVE_INTEGER = 'neginteger';
	case NEGATIVEZERO_INTEGER = 'negzerointeger';
	case POSITIVE_INTEGER = 'posinteger';
	case POSITIVEZERO_INTEGER = 'poszerointeger';
	case TEXTAREA = 'textarea';
	case LOWERCASE_WORD = 'lower_word';
	case UPPERCASE_WORD = 'upper_word';
	case WORD = 'word';
}
