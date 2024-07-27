<?php

namespace chsxf\MFX\Routers;

use chsxf\MFX\Services\ICoreServiceProvider;

/**
 * Base class for routers that need access to the various services
 * @author Christophe SAUVEUR <chsxf.pro@gmail.com>
 */
abstract class BaseRouteProvider implements IRouteProvider
{
    /**
     * Constructor 
     * @param ICoreServiceProvider $serviceProvider Core service provider instance
     */
    public function __construct(protected readonly ICoreServiceProvider $serviceProvider)
    {
    }
}
