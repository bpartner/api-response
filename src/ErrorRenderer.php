<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ErrorRenderer
{
    public static function resolve(Throwable $e): Responsable
    {
        $type = class_basename($e);

        $code = match ($type) {
            'AuthenticationException'                         => Response::HTTP_UNAUTHORIZED,
            'AccessDeniedHttpException'                       => Response::HTTP_FORBIDDEN,
            'ModelNotFoundException', 'NotFoundHttpException' => Response::HTTP_NOT_FOUND,
            'ValidationException'                             => Response::HTTP_UNPROCESSABLE_ENTITY,
            default                                           => Response::HTTP_INTERNAL_SERVER_ERROR,
        };


        return API::error(
            $e,
            status: $code,
            meta: ['exception' => class_basename($e)],
        );
    }
}
