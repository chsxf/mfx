<?php

/**
 * Data validation field type enum class
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX\DataValidator;

/**
 * Enum of all available data validation types
 * @since 1.0
 */
enum FieldType: string
{
    // Built-in HTML input types
    /** @since 1.0 */
    case CHECKBOX = 'checkbox';
    /** @since 1.0 */
    case COLOR = 'color';
    /** @since 1.0 */
    case DATE = 'date';
    /** @since 1.0 */
    case EMAIL = 'email';
    /** @since 1.0 */
    case FILE = 'file';
    /** @since 1.0 */
    case HIDDEN = 'hidden';
    /** @since 1.0 */
    case MONTH = 'month';
    /** @since 1.0 */
    case NUMBER = 'number';
    /** @since 1.0 */
    case RADIO = 'radio';
    /** @since 1.0 */
    case RANGE = 'range';
    /** @since 1.0 */
    case SELECT = 'select';
    /** @since 1.0 */
    case TEL = 'tel';
    /** @since 1.0 */
    case TEXT = 'text';
    /** @since 1.0 */
    case TIME = 'time';
    /** @since 1.0 */
    case URL = 'url';
    /** @since 1.0 */
    case WEEK = 'week';
    /** @since 1.0 */
    case PASSWORD = 'password';

    // Custom type
    /** @since 1.0 */
    case INTEGER = 'integer';
    /** @since 1.0 */
    case MULTI_SELECT = 'multiselect';
    /** @since 1.0 */
    case NEGATIVE_INTEGER = 'neginteger';
    /** @since 1.0 */
    case NEGATIVEZERO_INTEGER = 'negzerointeger';
    /** @since 1.0 */
    case POSITIVE_INTEGER = 'posinteger';
    /** @since 1.0 */
    case POSITIVEZERO_INTEGER = 'poszerointeger';
    /** @since 1.0 */
    case TEXTAREA = 'textarea';
    /** @since 1.0 */
    case LOWERCASE_WORD = 'lower_word';
    /** @since 1.0 */
    case UPPERCASE_WORD = 'upper_word';
    /** @since 1.0 */
    case WORD = 'word';
}
