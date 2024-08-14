<?php

declare(strict_types=1);

namespace chsxf\MFX\Services\Proxies;

use chsxf\MFX\Config;
use chsxf\MFX\Services\IConfigService;

/**
 * @since 2.0
 * @ignore
 */
final class ConfigServiceProxy implements IConfigService
{
    public function __construct(private readonly IConfigService $configService)
    {
    }

    public function load(Config $configData, string $domain = self::DEFAULT_DOMAIN)
    {
        $this->configService->load($configData, $domain);
    }

    public function tryGetValue(string $property, mixed &$outValue, ?string $domain = null): bool
    {
        return $this->configService->tryGetValue($property, $outValue, $domain);
    }

    public function getValue(string $property, mixed $default = null, ?string $domain = null): mixed
    {
        return $this->configService->getValue($property, $default, $domain);
    }

    public function hasValue(string $property, ?string $domain = null): bool
    {
        return $this->configService->hasValue($property, $domain);
    }
}
