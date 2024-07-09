<?php

/**
 * Class and helper functions for network management
 *
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */

namespace chsxf\MFX;

/**
 * Network management helper class
 * @since 1.0
 */
final class NetworkTools
{
    private const LOOKUP_TABLE = array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    private const DEFAUL_IP = '::1';

    /**
     * Retrieves the remote IP address based on the environment variables
     * @since 1.0
     * @return string the remote IP address or IPv6 localhost if not found
     */
    public static function getRemoteIP(): string
    {
        $env = getenv();
        foreach (self::LOOKUP_TABLE as $lookup) {
            if (!empty($env[$lookup])) {
                return $env[$lookup];
            }
        }
        return self::DEFAUL_IP;
    }
}
