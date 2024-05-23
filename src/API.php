<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Bpartner\ApiResponse\ResponseFactory
 */
final class API extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'apiResponse';
    }
}
