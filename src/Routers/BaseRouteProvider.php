<?php

namespace chsxf\MFX\Routers;

use chsxf\MFX\Services\ICoreServiceProvider;

abstract class BaseRouteProvider implements IRouteProvider
{
    public function __construct(protected readonly ICoreServiceProvider $serviceProvider)
    {
    }
}
