<?php

namespace chsxf\MFX;

use chsxf\MFX\Exceptions\ConfigException;

/**
 * Configuration data structure
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 * @since 2.0
 */
final class Config
{
    private const KEY_REGEX = '/^[a-z0-9_]+$/i';

    /**
     * Constructor
     * @param array $data Config data as an associative array
     */
    public function __construct(public readonly array $data = array())
    {
        $this->validateData($data);
    }

    private function validateData(array $dataArray): void
    {
        foreach ($dataArray as $k => $v) {
            if (!preg_match(self::KEY_REGEX, $k)) {
                throw new ConfigException("'{$k}' is not a valid config data key");
            }

            if (is_array($v)) {
                $this->validateData($v);
            }
        }
    }
}
